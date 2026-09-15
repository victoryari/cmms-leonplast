import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'database_helper.dart';

class SyncService {
  static final SyncService instance = SyncService._init();
  SyncService._init();

  final String baseUrl = 'http://10.0.2.2:8000/api/v1'; // IP de emulador o servidor local (127.0.0.1 en dispositivo físico)
  bool isSyncing = false;

  /// Inicializar el escuchador de conectividad a la red
  void initConnectivityListener(String authToken) {
    Connectivity().onConnectivityChanged.listen((List<ConnectivityResult> results) async {
      bool hasConnection = results.any((r) => r != ConnectivityResult.none);
      if (hasConnection && !isSyncing) {
        await processSyncCycle(authToken);
      }
    });
  }

  /// Ciclo completo de Sincronización (Outbox Flush + Delta Sync Pull)
  Future<void> processSyncCycle(String authToken) async {
    isSyncing = true;
    try {
      // 1. Flush de la cola de acciones capturadas offline
      await flushOutboxQueue(authToken);

      // 2. Pull de cambios Delta desde Laravel
      await pullDeltaSync(authToken);
    } catch (e) {
      print('Error durante ciclo de sincronización: $e');
    } finally {
      isSyncing = false;
    }
  }

  /// Vaciar cola Outbox acumulada en SQLite enviando las peticiones a la API REST de Laravel
  Future<void> flushOutboxQueue(String authToken) async {
    final pendingItems = await DatabaseHelper.instance.getPendingQueue();

    for (var item in pendingItems) {
      final int itemId = item['id'];
      final String endpoint = item['endpoint'];
      final String method = item['method'];
      final Map<String, dynamic> payload = jsonDecode(item['payload']);

      try {
        http.Response response;
        final headers = {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $authToken',
        };

        if (method == 'POST') {
          response = await http.post(
            Uri.parse('$baseUrl$endpoint'),
            headers: headers,
            body: jsonEncode(payload),
          );
        } else {
          response = await http.put(
            Uri.parse('$baseUrl$endpoint'),
            headers: headers,
            body: jsonEncode(payload),
          );
        }

        if (response.statusCode >= 200 && response.statusCode < 300) {
          // Procesado exitosamente en el servidor central -> Eliminar de la cola local
          await DatabaseHelper.instance.deleteFromQueue(itemId);
        }
      } catch (e) {
        print('Fallo al procesar item $itemId de la cola offline: $e');
      }
    }
  }

  /// Descargar cambios delta desde Laravel desde la última fecha registrada
  Future<List<Map<String, dynamic>>> pullDeltaSync(String authToken) async {
    final prefs = await SharedPreferences.getInstance();
    final String? lastSync = prefs.getString('last_sync_timestamp');

    final String url = (lastSync != null && lastSync.isNotEmpty)
        ? '$baseUrl/ordenes-trabajo/sync?since=$lastSync'
        : '$baseUrl/ordenes-trabajo/sync';

    try {
      final response = await http.get(
        Uri.parse(url),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $authToken',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final List<dynamic> serverOts = data['data'] ?? [];
        final String newTimestamp = data['server_timestamp'] ?? DateTime.now().toIso8601String();

        // Guardar en la base de datos local SQLite
        await DatabaseHelper.instance.saveLocalWorkOrders(serverOts);
        await prefs.setString('last_sync_timestamp', newTimestamp);

        return await DatabaseHelper.instance.getLocalWorkOrders();
      }
    } catch (e) {
      print('Sin conexión al servidor. Cargando OTs desde SQLite local.');
    }

    // Retornar OTs almacenadas en el teléfono si no hay red
    return await DatabaseHelper.instance.getLocalWorkOrders();
  }
}
