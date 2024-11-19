<?php
// เริ่มต้นเซสชัน
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ฟังก์ชันหลักสำหรับดึง Metadata และตัวอย่างข้อมูล
function getDatabaseMetadataAndSamples($conn, $databaseName, $sampleLimit = 2)
{
    $excludedTables = getExcludedTables();

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
    ];

    return [
        'metadata' => fetchMetadata($conn, $databaseName, $excludedTables, $excludedColumnsByTable),
        'samples' => fetchSampleData($conn, $excludedTables, $sampleLimit, $excludedColumnsByTable)
    ];
}

// ฟังก์ชันดึงรายชื่อตารางที่ยกเว้น
function getExcludedTables()
{
    return [
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

// ฟังก์ชันสร้าง Prompt สำหรับ ChatGPT
// function buildChatGPTPrompt($message, $filteredMetadata, $sampleData, $isJoinRequired = false)
// {
//     $prompt = "You are an advanced AI assistant. Based on the provided database structure and sample data, generate an SQL query to answer the following question.\n\n";
//     $prompt .= "**Database Metadata**:\n";

//     foreach ($filteredMetadata as $table => $tableData) {
//         $prompt .= "- Table: $table\n";
//         $prompt .= "  Description: {$tableData['description']}\n";
//         $prompt .= "  Columns:\n";
//         foreach ($tableData['columns'] as $column => $colData) {
//             $prompt .= "    - $column ({$colData['type']}): {$colData['description']}\n";
//         }
//     }

//     $prompt .= "\n**Sample Data**:\n";
//     foreach ($sampleData as $table => $rows) {
//         if (isset($filteredMetadata[$table])) {
//             $prompt .= "- $table: " . json_encode($rows) . "\n";
//         }
//     }

//     $prompt .= "\n**Question**:\n";
//     $prompt .= "\"$message\"\n\n";
//     $prompt .= "**Instructions**:\n";
//     $prompt .= "- Create an SQL query based on the provided database structure and sample data.\n";
//     $prompt .= "- Ensure the query is optimized and includes conditions explicitly mentioned in the question.\n";
//     $prompt .= "- Only include necessary columns and avoid redundant data.\n";

//     if ($isJoinRequired) {
//         $prompt .= "- Use JOIN operations if required.\n";
//     }

//     return $prompt;
// }

// function buildChatGPTPrompt($message, $filteredMetadata, $sampleData, $isJoinRequired = false)
// {
//     $prompt = "You are an advanced AI assistant. Based on the provided database structure and sample data, generate an SQL query to answer the following question.\n\n";
    
//     $prompt .= "**Database Metadata**:\n";
//     foreach ($filteredMetadata as $table => $tableData) {
//         $prompt .= "- Table: $table\n";
//         $prompt .= "  Description: {$tableData['description']}\n";
//         $prompt .= "  Columns (only necessary ones):\n";
//         foreach ($tableData['columns'] as $column => $colData) {
//             $prompt .= "    - $column ({$colData['type']}): {$colData['description']}\n";
//         }
//     }

//     $prompt .= "\n**Sample Data** (only necessary rows):\n";
//     foreach ($sampleData as $table => $rows) {
//         if (isset($filteredMetadata[$table])) {
//             $prompt .= "- $table: " . json_encode($rows) . "\n";
//         }
//     }

//     $prompt .= "\n**Question**:\n";
//     $prompt .= "\"$message\"\n\n";

//     $prompt .= "**Instructions**:\n";
//     $prompt .= "- Analyze the provided database metadata and sample data.\n";
//     $prompt .= "- Identify only the necessary tables and columns required to answer the question.\n";
//     $prompt .= "- Generate an optimized SQL query that includes only relevant tables and columns.\n";
//     $prompt .= "- Exclude unnecessary columns or tables from the query.\n";
//     $prompt .= "- Include JOIN operations if explicitly required by the question or if data from multiple tables is needed.\n";

//     return $prompt;
// }

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

// ฟังก์ชันจัดการผลลัพธ์การ query และการแสดงผล
// function processChatGPTResponse($conn, $query, $metadata, $tableNames)
// {
//     $result = $conn->query($query);
//     if (!$result || $result->num_rows === 0) return ['columns' => [], 'rows' => []];

//     $columns = array_keys($result->fetch_assoc());
//     $result->data_seek(0); // รีเซ็ต pointer

//     echo '<pre>';

//     $data = [
//         'columns' => $columns,
//         'rows' => $result->fetch_all(MYSQLI_ASSOC)
//     ];

//     echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
//     exit();

//     return [
//         'columns' => $columns,
//         'rows' => $result->fetch_all(MYSQLI_ASSOC)
//     ];
// }

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

// ฟังก์ชันแยกคำถาม
function splitQuestionsWithChatGPT($text)
{
    $prompt = "Please split the following text into separate questions. If there is more than one question, separate each question by a new line:\n\n\"$text\"";
    $response = callChatGPTAPI($prompt);
    return array_filter(array_map('trim', explode("\n", $response)));
}

// ฟังก์ชันจัดรูปแบบผลลัพธ์
// function formatResponseWithChatGPT($queryResult) {
//     $prompt = "Format the following database query result in Thai using HTML tags for clear presentation. Provide only the inner HTML tags and content without enclosing <html> or <body> tags.\n\n";
//     $prompt .= "Query Result:\n$queryResult\n\n";
//     $prompt .= "Ensure the response is well-structured with HTML tags for readability, including line breaks and necessary spacing. Display only the content as inner HTML tags.";

//     return callChatGPTAPI($prompt);
// }

// function formatResponseWithChatGPT($queryResult)
// {
//     $prompt = "Based on the following database query result, generate a beautiful and clear response in Thai as a well-structured sentence. Avoid raw data formatting like tables or JSON. Instead, explain the result clearly as natural text.\n\n";
//     $prompt .= "Query Result:\n$queryResult\n\n";

//     return callChatGPTAPI($prompt);
// }

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

// // ฟังก์ชันดึงคำตอบไดนามิกด้วย ChatGPT
// function getDynamicAnswerWithChatGPT($conn, $message, $session_id)
// {
//     try {
//         $questions = splitQuestionsWithChatGPT($message);
//         $finalAnswer = '';

//         // foreach ($questions as $individualQuestion) {
//         $individualQuestion = $message;
//         list($metadata, $sampleData) = getDatabaseMetadataAndSamples($conn, 'usedcar');
//         $filteredMetadata = filterMetadataByQuestion($metadata, $individualQuestion);
//         $isJoinRequired = count($filteredMetadata) > 1;

//         $prompt = buildChatGPTPrompt($individualQuestion, $filteredMetadata, $sampleData, $isJoinRequired);

//         $response = callChatGPTAPI($prompt);

//         // print_r($response); exit();

//         $query = extractQueryFromResponse($response);
//         // echo '1'; exit();
//         if (!$query) throw new Exception("ไม่พบ SQL query สำหรับคำถามนี้");
//         // echo '1'; exit();
//         // echo '1'; exit(); 10วิ
//         $queryResult = processChatGPTResponse($conn, $query, $metadata, array_keys($filteredMetadata));
//         // echo '1'; exit(); ไว
//         // $answerFromGPT = formatResponseWithChatGPT($queryResult);
//         $answerFromGPT = $queryResult;
//         // echo '1'; exit(); ช้าเลย
//         $html = '';

//         // สร้างตาราง HTML
//         $html .= "<table border='1'>";
//         $html .= "<tr>";
//         foreach ($queryResult['columns'] as $column) {
//             $html .= "<th>$column</th>";
//         }
//         $html .= "</tr>";

//         foreach ($queryResult['rows'] as $row) {
//             $html .= "<tr>";
//             foreach ($row as $value) {
//                 $html .= "<td>$value</td>";
//             }
//             $html .= "</tr>";
//         }
//         $html .= "</table>";

//         // $finalAnswer = $html;

//         $answerFromGPT = formatResponseWithChatGPT(json_encode($queryResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
//         $finalAnswer = $answerFromGPT;

//         logPromptData($individualQuestion, $prompt, $response, $queryResult, $answerFromGPT);
//         // }

//         return $finalAnswer;
//     } catch (Exception $e) {
//         return getGeneralAnswer($message);
//     }
// }

// ฟังก์ชันดึงคำตอบไดนามิกด้วย ChatGPT
function getDynamicAnswerWithChatGPT($conn, $message, $session_id)
{
    try {
        // ดึง Metadata และตัวอย่างข้อมูล
        $data = getDatabaseMetadataAndSamples($conn, 'usedcar');
        $filteredMetadata = filterMetadataByQuestion($data['metadata'], $message);
        $isJoinRequired = count($filteredMetadata) > 1;

        // สร้าง Prompt
        $prompt = buildChatGPTPrompt($message, $filteredMetadata, $data['samples'], $isJoinRequired);

        // เรียก API
        $response = callChatGPTAPI($prompt);
        $query = extractQueryFromResponse($response);

        if (!$query) throw new Exception("ไม่พบ SQL query สำหรับคำถามนี้");

        // ดึงผลลัพธ์
        $queryResult = processChatGPTResponse($conn, $query, $data['metadata'], array_keys($filteredMetadata));

        $answerFromGPT = formatResponseWithChatGPT($message, json_encode($queryResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        logPromptData($message, $prompt, $response, $queryResult, $answerFromGPT);

        return $answerFromGPT;
    } catch (Exception $e) {
        return getGeneralAnswer($message);
    }
}
