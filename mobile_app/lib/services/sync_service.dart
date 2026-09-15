import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'database_helper.dart';

class SyncService {
  static final SyncService instance = SyncService._init();
  SyncService._init();

  /// URL Base Dinámica según plataforma (Navegador Web Chrome o Emulador Android)
  String get baseUrl => kIsWeb ? 'http://localhost:8000/api/v1' : 'http://10.0.2.2:8000/api/v1';

  bool isSyncing = false;

  /// Autenticar técnico mediante API REST de Laravel
  Future<bool> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': email,
          'password': password,
          'device_name': kIsWeb ? 'chrome-web-app' : 'android-flutter-app',
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          final token = data['token'];
          final user = data['user'] ?? {};

          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('auth_token', token);
          await prefs.setString('user_name', user['nombre_completo'] ?? user['email'] ?? 'Técnico');
          await prefs.setString('user_email', user['email'] ?? '');
          await prefs.setString('user_role', user['rol'] ?? 'Tecnico');

          // Ejecutar sincronización inicial tras login
          await processSyncCycle(token);
          return true;
        }
      }
    } catch (e) {
      debugPrint('Error en login API: $e');
    }
    return false;
  }

  /// Verificar si existe token guardado
  Future<String?> getAuthToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  /// Obtener nombre del usuario activo
  Future<String> getUserName() async {
    final prefs = await SharedPreferences.getInstance();
    final name = prefs.getString('user_name') ?? 'Técnico';
    final role = prefs.getString('user_role') ?? 'Tecnico';
    return '$name ($role)';
  }

  /// Cerrar sesión
  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('user_name');
    await prefs.remove('user_role');
    await prefs.remove('last_sync_timestamp');
  }

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
      debugPrint('Error durante ciclo de sincronización: $e');
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
        debugPrint('Fallo al procesar item $itemId de la cola offline: $e');
      }
    }
  }

  /// Descargar cambios delta desde Laravel desde la última fecha registrada
  Future<List<Map<String, dynamic>>> pullDeltaSync(String authToken) async {
    final prefs = await SharedPreferences.getInstance();
    final String? lastSync = prefs.getString('last_sync_timestamp');

    final String url = (lastSync != null && lastSync.isNotEmpty)
        ? '$baseUrl/ordenes-trabajo/sync?since=${Uri.encodeComponent(lastSync)}'
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
      debugPrint('Sin conexión al servidor. Cargando OTs desde SQLite local.');
    }

    // Retornar OTs almacenadas en el teléfono si no hay red
    return await DatabaseHelper.instance.getLocalWorkOrders();
  }
}
