<?php
// เริ่มต้นเซสชัน
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('CACHE_FILE', 'question_cache.json');
define('CACHE_TIMESTAMP_FILE', 'cache_timestamp.txt');
define('CACHE_EXPIRY_TIME', 600); // 10 นาที (600 วินาที)

/**
 * ตรวจสอบว่าแคชหมดอายุหรือไม่
 */
function isCacheExpired()
{
    if (!file_exists(CACHE_TIMESTAMP_FILE)) {
        return true; // หากไม่มีไฟล์ timestamp ให้ถือว่าแคชหมดอายุ
    }

    $lastCacheTime = (int) file_get_contents(CACHE_TIMESTAMP_FILE);
    $currentTime = time();

    return ($currentTime - $lastCacheTime) >= CACHE_EXPIRY_TIME;
}

/**
 * ล้างแคช
 */
function clearCacheFile()
{
    if (file_exists(CACHE_FILE)) {
        unlink(CACHE_FILE);
    }

    if (file_exists(CACHE_TIMESTAMP_FILE)) {
        unlink(CACHE_TIMESTAMP_FILE);
    }
}

/**
 * อัปเดตเวลาแคชล่าสุด
 */
function updateCacheTimestamp()
{
    file_put_contents(CACHE_TIMESTAMP_FILE, time());
}

/**
 * ตรวจสอบคำถามในแคช
 */
function getCachedAnswerFromFile($question)
{
    // ตรวจสอบว่าแคชหมดอายุหรือไม่
    if (isCacheExpired()) {
        clearCacheFile();
        return null; // ล้างแคชและไม่คืนคำตอบเก่า
    }

    if (!file_exists(CACHE_FILE)) {
        return null;
    }

    $cache = json_decode(file_get_contents(CACHE_FILE), true);
    return $cache[$question] ?? null;
}

/**
 * บันทึกคำถามและคำตอบลงในแคชไฟล์
 */
function cacheAnswerToFile($question, $answer)
{
    // ตรวจสอบว่าแคชหมดอายุหรือไม่
    if (isCacheExpired()) {
        clearCacheFile(); // ล้างแคช
    }

    $cache = file_exists(CACHE_FILE) ? json_decode(file_get_contents(CACHE_FILE), true) : [];
    $cache[$question] = $answer;

    // บันทึกกลับไปที่ไฟล์
    file_put_contents(CACHE_FILE, json_encode($cache, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    // อัปเดตเวลาล่าสุด
    updateCacheTimestamp();
}


// ฟังก์ชันหลักสำหรับดึง Metadata และตัวอย่างข้อมูล
function getDatabaseMetadataAndSamples($conn, $databaseName, $sampleLimit = 2)
{
    // ยกเว้นตาราง
    $excludedTables =  [
        'acclimatization_place',
        'autoloan_targeted',
        'car_document',
        'car_stock_document',
        'car_stock_look',
        'car_stock_picture_fix',
        'car_stock_picture_other',
        'car_stock_options',
        'book_registration',
        'book_registration_list',
        'book_registration_location',
        'employee_setting_status',
        'lp_abouts',
        'lp_contacts',
        'lp_galleries',
        'lp_knowledges',
        'lp_promotions',
        'lp_rests',
        'lp_websites',
        'notify',
        'migrations',
        'provinces',
        'setting_autoloan',
        'setting_autoloan_logs',
        'setting_autoloan_report',
        'setting_car_stock_table',
        'system_logs',
        'targeted'
    ];

    // ยกเว้นฟิว
    $excludedColumnsByTable = [
        'car_stock' => [
            'car_stock_name',
            'car_stock_car_brand',
            'car_stock_car_model',
            'car_stock_car_sub_type',
            'car_stock_remark',
            'car_stock_condition',
            'car_stock_img',
            'promotion_id',
            'car_stock_location',
            'car_stock_location_date_at',
            'car_stock_created_by',
            'url_3d',
            'url_space',
            'car_stock_car_year',
            'car_stock_car_color',
            'car_stock_car_color',
            'car_stock_car_gear',
        ],
        'car_stock_detail_buy' => [
            'car_stock_detail_buy_type',
            'car_stock_detail_buy_floor',
            'car_stock_detail_buy_price_in',
            'car_stock_detail_buy_price_fp',
            'car_stock_detail_buy_vat',
            'car_stock_detail_buy_bid_address',
            'car_stock_detail_buy_ower',
            'car_stock_detail_buy_name',
            'car_stock_detail_buy_additional_note',
            'car_stock_detail_buy_bid_date',
            'car_stock_detail_buy_exprired_tax_date',
            'car_stock_detail_buy_exprired_act_date',
            'car_stock_detail_buy_exprired_insurance_date',
            'car_stock_detail_buy_car_key',
            'car_stock_detail_buy_branch',
            'car_stock_detail_buy_fiance_total',
            'car_stock_detail_buy_sale_no_vat',
            'car_stock_detail_buy_sale_dow',
            'car_stock_detail_buy_brand_thai',
            'car_stock_detail_buy_car_installments',
            'car_stock_detail_buy_car_number_installments',
            'car_stock_detail_buy_net_capital',
            'car_stock_detail_buy_other_cost',
            'car_stock_detail_buy_mileage',
            'car_stock_detail_buy_date_car_rebuild_status',
            'car_stock_detail_buy_date_car_doc_status',
            'car_stock_detail_buy_created_at',
            'car_stock_detail_buy_updated_at',
            'car_stock_detail_buy_created_by',
        ],
        'car_stock_owner' => [
            'car_stock_owner_name',
            'car_stock_owner_address',
            'car_stock_owne_card_id',
            'car_stock_owner_car_type',
            'car_stock_owner_car_look',
            // 'car_stock_owner_car_tank_number',
            // 'car_stock_owner_car_tank_engine_number', // เลข
            // 'car_stock_owner_car_fuel', // เลข
            // 'car_stock_owner_tel',
            'car_stock_owner_date_registration',
            'car_stock_owner_car_condition',
            'car_stock_owner_car_remark',
            'car_stock_owner_book_src',
            'car_stock_owner_car_weight',
            'car_stock_owner_car_manual',
            'car_stock_owner_car_number_owner',
            'car_stock_owner_car_responsibility',
            'car_stock_owner_car_experience',
            'car_stock_owner_car_engine_size',
            'car_stock_owner_created_by',
            'car_stock_owner_created_at',
            'car_stock_owner_updated_at',
        ],
        'car_stock_finance' => [
            'car_stock_finance_kbank',
            'car_stock_finance_scb',
            'car_stock_finance_bay',
            'car_stock_finance_kkp',
            'car_stock_finance_maket',
            'car_stock_finance_cimb',
            'car_stock_finance_oalt',
            'car_stock_finance_tlt',
            'car_stock_finance_created_at',
            'car_stock_finance_updated_at',
            'car_stock_finance_created_by',
        ],
        // 'documents' => [
        //     'id',
        //     'doc_type',
        //     'doc_number',
        //     'doc_date',
        //     'title',
        //     'price',
        //     'car_stock_id',
        //     'car_title',
        //     'customer_id',
        //     'customer_title',
        //     'seller_id',
        //     'seller_title',
        //     'doc_payment_type',
        //     'doc_payment_type_etc',
        //     'cash_flow_name',
        //     'cheque_nam',
        //     'cheque_ref',
        //     'cheque_bank_title',
        //     'cheque_bank_branch',
        //     'cheque_bank_no',
        //     'cheque_bank_date',
        //     'employee_id',
        //     'username',
        //     'filePath',
        //     'not',
        //     'doc_detail',
        //     'reference_number',
        //     'price_vat',
        //     'doc_vat',
        //     'doc_wht',
        //     'wht_percent',
        //     'created_at',
        //     'updated_at',
        //     'deleted_at',
        // ]
        'bookings' => [
            'customer_grade',
            'giveaway_list',
            'booking_note',
        ]
    ];

    return [
        'metadata' => fetchMetadata($conn, $databaseName, $excludedTables, $excludedColumnsByTable),
        'samples' => fetchSampleData($conn, $excludedTables, $sampleLimit, $excludedColumnsByTable)
    ];
}

function fetchMetadata($conn, $databaseName, $excludedTables = [], $excludedColumnsByTable = [])
{
    $metadata = [];
    $query = "
        SELECT T.TABLE_NAME, T.TABLE_COMMENT, C.COLUMN_NAME, C.DATA_TYPE, C.COLUMN_COMMENT
        FROM information_schema.TABLES AS T
        JOIN information_schema.COLUMNS AS C ON T.TABLE_NAME = C.TABLE_NAME
        WHERE T.TABLE_SCHEMA = '$databaseName';
    ";
    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $tableName = $row['TABLE_NAME'];
            $columnName = $row['COLUMN_NAME'];

            // ข้ามตารางที่ไม่ต้องการ
            if (in_array($tableName, $excludedTables)) continue;

            // ข้ามคอลัมน์เฉพาะของตารางนั้น ๆ
            if (isset($excludedColumnsByTable[$tableName]) && in_array($columnName, $excludedColumnsByTable[$tableName])) {
                continue;
            }

            // เพิ่มข้อมูลใน Metadata
            $metadata[$tableName]['description'] = $row['TABLE_COMMENT'] ?: '';
            $metadata[$tableName]['columns'][$columnName] = [
                'type' => $row['DATA_TYPE'],
                'description' => $row['COLUMN_COMMENT'] ?: ''
            ];
        }
    }
    return $metadata;
}

function fetchSampleData($conn, $excludedTables = [], $sampleLimit = 2, $excludedColumnsByTable = [])
{
    $sampleData = [];
    $tables = $conn->query("SHOW TABLES");

    while ($table = $tables->fetch_array()) {
        $tableName = $table[0];

        // ข้ามตารางที่ไม่ต้องการ
        if (in_array($tableName, $excludedTables)) continue;

        // ดึงข้อมูลคอลัมน์ทั้งหมดในตาราง
        $columnsQuery = $conn->query("SHOW COLUMNS FROM $tableName");
        $columns = [];

        while ($column = $columnsQuery->fetch_assoc()) {
            $columnName = $column['Field'];

            // ข้ามคอลัมน์ที่ไม่ต้องการ
            if (isset($excludedColumnsByTable[$tableName]) && in_array($columnName, $excludedColumnsByTable[$tableName])) {
                continue;
            }

            $columns[] = $columnName;
        }

        // ถ้าไม่มีคอลัมน์ที่เหลือ ให้ข้ามตารางนี้
        if (empty($columns)) continue;

        // สร้างคำสั่ง SQL ดึงตัวอย่างข้อมูลเฉพาะคอลัมน์ที่เหลือ
        $columnsList = implode(',', $columns);
        $query = "SELECT $columnsList FROM $tableName LIMIT $sampleLimit";
        $result = $conn->query($query);

        if ($result) {
            $sampleData[$tableName] = $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    return $sampleData;
}

// ฟังก์ชันกรอง Metadata โดยคำถาม
function filterMetadataByQuestion($metadata, $question)
{
    $patterns = getPatterns();
    $relevantTables = [];

    foreach ($patterns as $details) {
        if (preg_match($details['pattern'], $question)) {
            foreach ($details['related_tables'] as $table) {
                if (isset($metadata[$table])) {
                    $relevantTables[$table] = $metadata[$table];
                }
            }
        }
    }

    return !empty($relevantTables) ? $relevantTables : $metadata;
}

// ฟังก์ชันดึง Patterns สำหรับการแมตช์คำถาม
function getPatterns()
{
    return [
        // เกี่ยวกับสินเชื่อ
        [
            'pattern' => '/(สินเชื่อ|กู้)/',
            'related_tables' => ['autoloan', 'autoloan_payment', 'autoloan_running', 'customers', 'employees', 'setting_autoloan']
        ],
        [
            'pattern' => '/(รายละเอียดสินเชื่อ|รายละเอียดกู้|งวด)/',
            'related_tables' => ['autoloan', 'autoloan_payment']
        ],

        // เกี่ยวกับการจอง
        [
            'pattern' => '/(จอง|การจอง|ชำระ|การชำระ)/',
            'related_tables' => ['bookings', 'branch', 'car_stock', 'customers']
        ],

        // เกี่ยวกับรถ
        [
            'pattern' => '/(รถ|รถยนต์|รุ่น|ยี่ห้อ|ปี|สี|เกียร์|ไมล์|เลขทะเบียน|สต็อก|ราคา)/',
            'related_tables' => ['car_stock', 'car_stock_detail_buy', 'car_stock_owner', 'car_stock_finance', 'documents', 'bookings']
        ],

        // เกี่ยวกับลูกค้า
        [
            'pattern' => '/(ลูกค้า|ผู้ซื้อ)/',
            'related_tables' => ['customers', 'customer_types']
        ],

        // เกี่ยวกับใบสำคัญ
        [
            'pattern' => '/(ใบสำคัญ|ใบสำคัญรับ|ใบสำคัญจ่าย|ส่วนลด|รับ|จ่าย)/',
            'related_tables' => ['documents']
        ],
        [
            'pattern' => '/(รายรับ|รายจ่าย|ส่วนลด)/',
            'related_tables' => ['documents', 'car_stock']
        ],

        // เกี่ยวกับพนักงาน
        [
            'pattern' => '/(พนักงาน)/',
            'related_tables' => ['employees']
        ],

        // เกี่ยวกับไฟแนนซ์ 
        [
            'pattern' => '/(ไฟแนนซ์)/',
            'related_tables' => ['finances', 'car_stock']
        ],

        // etc.
        [
            'pattern' => '/(สาขา)/',
            'related_tables' => ['branch']
        ],
        [
            'pattern' => '/(ข้อความ|แชท|ประวัติ|คุย|พูดคุย)/',
            'related_tables' => ['chats', 'customers', 'employees']
        ],
        [
            'pattern' => '/(ผู้ขาย|seller)/',
            'related_tables' => ['sellers']
        ],
    ];
}

function buildChatGPTPrompt($message, $filteredMetadata, $sampleData, $isJoinRequired = false)
{
    $prompt = "You are an advanced AI assistant. Based on the provided database structure and sample data, generate an SQL query to answer the following question.\n\n";

    $prompt .= "**Database Metadata**:\n";
    foreach ($filteredMetadata as $table => $tableData) {
        $prompt .= "- Table: $table\n";
        $prompt .= "  Description: {$tableData['description']}\n";
        $prompt .= "  Columns:\n";
        foreach ($tableData['columns'] as $column => $colData) {
            $prompt .= "    - $column ({$colData['type']}): {$colData['description']}\n";
        }
    }

    $prompt .= "\n**Sample Data**:\n";
    foreach ($sampleData as $table => $rows) {
        if (isset($filteredMetadata[$table])) {
            $prompt .= "- $table: " . json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        }
    }

    $prompt .= "\n**Question**:\n";
    $prompt .= "\"$message\"\n\n";

    $prompt .= "**Instructions**:\n";
    $prompt .= "- Analyze the provided database metadata and sample data.\n";
    $prompt .= "- Include all relevant tables and columns in your query.\n";
    $prompt .= "- Ensure the query is optimized and includes conditions explicitly mentioned in the question.\n";

    if ($isJoinRequired) {
        $prompt .= "- Use JOIN operations if required to combine data from multiple tables.\n";
    }

    return $prompt;
}
function processChatGPTResponse($conn, $query, $metadata, $tableNames)
{
    $result = $conn->query($query);
    if (!$result || $result->num_rows === 0) return ['columns' => [], 'rows' => []];

    // ดึงข้อมูล Metadata เพื่อเช็ค comment
    $columns = array_keys($result->fetch_assoc());
    $result->data_seek(0); // รีเซ็ต pointer

    // สร้าง Mapping ชื่อคอลัมน์กับคำอธิบาย (comment)
    $columnDescriptions = [];
    foreach ($tableNames as $tableName) {
        if (isset($metadata[$tableName]['columns'])) {
            foreach ($metadata[$tableName]['columns'] as $columnName => $columnData) {
                $columnDescriptions[$columnName] = $columnData['description'] ?? $columnName; // ใช้ comment หากมี
            }
        }
    }

    // แทนที่ชื่อคอลัมน์ด้วย comment (หรือคงชื่อเดิมหากไม่มี comment)
    $columnsWithDescriptions = array_map(function ($col) use ($columnDescriptions) {
        return $columnDescriptions[$col] ?? $col; // ใช้ comment ถ้ามี หรือใช้ชื่อคอลัมน์แทน
    }, $columns);

    //     $data = [
    //     'columns' => $columnsWithDescriptions,
    //     'rows' => $result->fetch_all(MYSQLI_ASSOC)
    // ];

    // echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    // exit();

    return [
        'columns' => $columnsWithDescriptions,
        'rows' => $result->fetch_all(MYSQLI_ASSOC)
    ];
}

// ฟังก์ชันดึง SQL Query จาก ChatGPT
function extractQueryFromResponse($response)
{
    if (preg_match('/```sql(.*?)```/s', $response, $matches)) {
        return trim($matches[1]);
    } elseif (preg_match('/(SELECT .*?;)/is', $response, $matches)) {
        return trim($matches[1]);
    }
    return '';
}

// ฟังก์ชันเรียกใช้ ChatGPT API
function callChatGPTAPI($prompt)
{
    $openaiApiKey = $_ENV['OPEN_API_KEY'];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            "model" => $_ENV['OPEN_API_VERSION'], // "gpt-3.5-turbo", "gpt-4-turbo", gpt-4
            "messages" => [["role" => "user", "content" => $prompt]]
        ]),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer $openaiApiKey"
        ]
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);

    if (isset($responseData["choices"][0]["message"]["content"])) {
        return $responseData["choices"][0]["message"]["content"];
    } else {
        throw new Exception("Invalid response from ChatGPT API.");
    }
}

function formatResponseWithChatGPT($customerQuestion, $queryResult)
{
    // แปลงข้อมูลตารางเป็น JSON เพื่อส่งไป ChatGPT
    $tableData = json_encode($queryResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    // Prompt ที่ยืดหยุ่น
    $prompt = "คุณคือผู้ช่วย AI ที่เชี่ยวชาญในการอธิบายข้อมูลให้อยู่ในรูปแบบที่กระชับและเข้าใจง่าย " .
        "โปรดใช้ข้อมูลที่ได้รับเพื่อเขียนคำตอบให้ลูกค้าในลักษณะ:\n\n" .
        "1. ตอบคำถามในภาษาไทยที่สุภาพ กระชับ และเข้าใจง่าย\n" .
        "2. แปลงข้อมูลให้อยู่ในลักษณะเหมาะสม เช่น รายการหรือข้อความบรรยายที่ชัดเจน\n" .
        "3. หากข้อมูลมีหลายแถว ให้แยกแถวออกเป็นข้อ หรือรายการที่ชัดเจน\n" .
        "4. หากไม่มีข้อมูลที่เกี่ยวข้อง ให้แจ้งลูกค้าอย่างสุภาพว่า \"ไม่มีข้อมูลที่เกี่ยวข้องในขณะนี้\"\n" .
        "5. ตัดข้อมูลที่เป็นรหัสหรือละเอียดเกินไป (เช่น ID, รหัสข้อมูล) หากไม่จำเป็นต่อการทำความเข้าใจของลูกค้า\n\n" .
        "**คำถามของลูกค้า:**\n\"$customerQuestion\"\n\n" .
        "**ข้อมูลที่ได้รับ:**\n$tableData\n\n" .
        "โปรดสร้างคำตอบโดยแปลงข้อมูลให้อยู่ในรูปแบบที่เข้าใจง่ายที่สุด";

    return callChatGPTAPI($prompt);
}

// ฟังก์ชันคำตอบทั่วไป
function getGeneralAnswer($message)
{
    $prompt = "คุณคือ SmartAssist ช่วยตอบคำถามผู้ใช้ในลักษณะมิตรภาพ:\n\n\"$message\"";
    return callChatGPTAPI($prompt);
}

// ฟังก์ชันบันทึกข้อมูล
function logPromptData($message, $prompt, $response, $queryResult, $answerFromGPT)
{
    // กำหนดชื่อโฟลเดอร์สำหรับเก็บ Log
    $logDir = '../logs';

    // ตรวจสอบว่าโฟลเดอร์มีอยู่แล้วหรือไม่ ถ้าไม่มีให้สร้างใหม่
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true); // สร้างโฟลเดอร์ด้วยสิทธิ์อ่านเขียน
    }

    // กำหนดชื่อไฟล์ Log โดยเก็บไว้ในโฟลเดอร์ logs
    $logFile = $logDir . '/log_' . date("j.n.Y") . '.log';

    // สร้างเนื้อหา Log
    $log = "=======================================================================\n" .
        "คำถามจากผู้ใช้: $message - " . date("F j, Y, g:i a") . "\n" .
        "-------------------------\nPrompt ที่ส่งไป: $prompt\nSQL ที่ได้จาก ChatGPT: $response\n" .
        "-------------------------\nคำตอบที่ให้ User: $answerFromGPT\n" .
        "=======================================================================\n";

    // เขียน Log ลงไฟล์
    file_put_contents($logFile, $log, FILE_APPEND);
}

// ฟังก์ชันดึงคำตอบไดนามิกด้วย ChatGPT
function getDynamicAnswerWithChatGPT($conn, $message, $session_id)
{
    try {

        // ตรวจสอบคำถามในไฟล์แคชก่อน
        $cachedAnswer = getCachedAnswerFromFile($message);
        if ($cachedAnswer) {
            return $cachedAnswer;
        }

        // ดึง Metadata และตัวอย่างข้อมูล
        $data = getDatabaseMetadataAndSamples($conn, 'usedcar');
        $filteredMetadata = filterMetadataByQuestion($data['metadata'], $message);
        $isJoinRequired = count($filteredMetadata) > 1;

        // สร้าง Prompt
        $prompt = buildChatGPTPrompt($message, $filteredMetadata, $data['samples'], $isJoinRequired);

        // เรียก API เพื่อสร้าง SQL Query
        $response = callChatGPTAPI($prompt);
        $query = extractQueryFromResponse($response);

        if (!$query) throw new Exception("ไม่พบ SQL query สำหรับคำถามนี้");

        // ดึงผลลัพธ์จากฐานข้อมูล
        $queryResult = processChatGPTResponse($conn, $query, $data['metadata'], array_keys($filteredMetadata));

        // แปลงผลลัพธ์เป็นคำตอบที่เข้าใจง่าย
        $answerFromGPT = formatResponseWithChatGPT($message, json_encode($queryResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        // บันทึกคำถามและคำตอบลงในไฟล์แคช
        cacheAnswerToFile($message, $answerFromGPT);

        // บันทึกข้อมูล Log
        logPromptData($message, $prompt, $response, $queryResult, $answerFromGPT);

        return $answerFromGPT;
    } catch (Exception $e) {
        return getGeneralAnswer($message);
    }
}
