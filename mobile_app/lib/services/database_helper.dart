import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import 'dart:convert';

class DatabaseHelper {
  static final DatabaseHelper instance = DatabaseHelper._init();
  static Database? _database;

  DatabaseHelper._init();

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDB('cmms_leonplast_offline.db');
    return _database!;
  }

  Future<Database> _initDB(String filePath) async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, filePath);

    return await openDatabase(
      path,
      version: 1,
      onCreate: _createDB,
    );
  }

  Future _createDB(Database db, int version) async {
    // 1. Tabla de Cola de Sincronización (Outbox Pattern)
    await db.execute('''
      CREATE TABLE sync_queue (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        endpoint TEXT NOT NULL,
        method TEXT NOT NULL,
        payload TEXT NOT NULL,
        retries INTEGER DEFAULT 0,
        created_at TEXT NOT NULL
      )
    ''');

    // 2. Tabla Espejo de Órdenes de Trabajo para Lectura/Escritura Offline
    await db.execute('''
      CREATE TABLE local_work_orders (
        id INTEGER PRIMARY KEY,
        codigo_ot TEXT NOT NULL,
        titulo TEXT NOT NULL,
        descripcion TEXT,
        estado TEXT NOT NULL,
        prioridad TEXT NOT NULL,
        tipo_ot TEXT NOT NULL,
        activo_nombre TEXT,
        tecnico_id INTEGER,
        solicitante_nombre TEXT,
        requiere_permiso_especial INTEGER DEFAULT 0,
        firma_tecnico TEXT,
        diagnostico TEXT,
        solucion TEXT,
        updated_at TEXT NOT NULL,
        json_data TEXT NOT NULL
      )
    ''');

    // 3. Catálogo Espejo de Activos para escaneo QR/NFC Offline
    await db.execute('''
      CREATE TABLE local_assets (
        id INTEGER PRIMARY KEY,
        codigo_activo TEXT NOT NULL,
        nombre TEXT NOT NULL,
        nfc_tag_uid TEXT,
        qr_code TEXT,
        estado_operativo TEXT,
        ubicacion TEXT
      )
    ''');
  }

  // --- MÉTODOS DE LA COLA OUTBOX ---

  Future<int> addToSyncQueue(String endpoint, String method, Map<String, dynamic> payload) async {
    final db = await instance.database;
    return await db.insert('sync_queue', {
      'endpoint': endpoint,
      'method': method,
      'payload': jsonEncode(payload),
      'retries': 0,
      'created_at': DateTime.now().toIso8601String(),
    });
  }

  Future<List<Map<String, dynamic>>> getPendingQueue() async {
    final db = await instance.database;
    return await db.query('sync_queue', orderBy: 'id ASC');
  }

  Future<int> deleteFromQueue(int id) async {
    final db = await instance.database;
    return await db.delete('sync_queue', where: 'id = ?', whereArgs: [id]);
  }

  // --- MÉTODOS DE MANEJO DE OTS EN LOCAL ---

  Future<void> saveLocalWorkOrders(List<dynamic> workOrders) async {
    final db = await instance.database;
    final batch = db.batch();

    for (var ot in workOrders) {
      batch.insert(
        'local_work_orders',
        {
          'id': ot['id'],
          'codigo_ot': ot['codigo_ot'] ?? '',
          'titulo': ot['titulo'] ?? '',
          'descripcion': ot['descripcion'] ?? '',
          'estado': ot['estado'] ?? 'Pendiente',
          'prioridad': ot['prioridad'] ?? 'Media',
          'tipo_ot': ot['tipo_ot'] ?? 'Correctivo',
          'activo_nombre': ot['activo']?['nombre'] ?? 'Sin activo',
          'tecnico_id': ot['tecnico_id'],
          'solicitante_nombre': ot['solicitante']?['nombres'] ?? 'Solicitante',
          'requiere_permiso_especial': (ot['requiere_permiso_especial'] == true) ? 1 : 0,
          'firma_tecnico': ot['firma_tecnico'],
          'updated_at': ot['updated_at'] ?? DateTime.now().toIso8601String(),
          'json_data': jsonEncode(ot),
        },
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }

    await batch.commit(noResult: true);
  }

  Future<List<Map<String, dynamic>>> getLocalWorkOrders() async {
    final db = await instance.database;
    return await db.query('local_work_orders', orderBy: 'updated_at DESC');
  }

  Future<void> updateLocalOtStatus(int otId, String nuevoEstado, {String? firmaBase64}) async {
    final db = await instance.database;
    Map<String, dynamic> values = {
      'estado': nuevoEstado,
      'updated_at': DateTime.now().toIso8601String(),
    };
    if (firmaBase64 != null) {
      values['firma_tecnico'] = firmaBase64;
    }
    await db.update('local_work_orders', values, where: 'id = ?', whereArgs: [otId]);
  }

  Future close() async {
    final db = await instance.database;
    db.close();
  }
}
