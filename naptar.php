<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

// Jogosultság ellenőrzése
checkPageAccess();

// Inicializáljuk a változókat
$users = [];
$dbEvents = [];
$error = null;

// Adatbázis kapcsolat létrehozása
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("Kapcsolódási hiba: " . mysqli_connect_error());
}

// Projektek lekérése
$projects_sql = "SELECT 
    p.id,
    p.name,
    p.project_startdate,
    p.project_enddate,
    pt.name as type_name
FROM project p
LEFT JOIN project_type pt ON p.type_id = pt.id
WHERE p.company_id = ?";

$stmt = mysqli_prepare($conn, $projects_sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['company_id']);
mysqli_stmt_execute($stmt);
$projects_result = mysqli_stmt_get_result($stmt);

$projectEvents = [];
while ($project = mysqli_fetch_assoc($projects_result)) {
    $start = new DateTime($project['project_startdate']);
    $end = new DateTime($project['project_enddate']);
    $interval = new DateInterval('P1D');
    $dateRange = new DatePeriod($start, $interval, $end->modify('+1 day'));

    foreach ($dateRange as $date) {
        $projectEvents[] = [
            'event_date' => $date->format('Y-m-d'),
            'event_type' => 'project',
            'name' => $project['name'],
            'type_name' => $project['type_name']
        ];
    }
}

// Munkák lekérése
$works_sql = "SELECT 
    w.id,
    w.work_start_date,
    w.work_end_date,
    p.name as project_name,
    GROUP_CONCAT(DISTINCT CONCAT(u.lastname, ' ', u.firstname)) as workers
FROM work w
LEFT JOIN project p ON w.project_id = p.id
LEFT JOIN user u ON FIND_IN_SET(u.id, w.user_id)
WHERE w.company_id = ?
GROUP BY w.id";

$stmt = mysqli_prepare($conn, $works_sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['company_id']);
mysqli_stmt_execute($stmt);
$works_result = mysqli_stmt_get_result($stmt);

$workEvents = [];
while ($work = mysqli_fetch_assoc($works_result)) {
    $start = new DateTime($work['work_start_date']);
    $end = new DateTime($work['work_end_date']);
    $interval = new DateInterval('P1D');
    $dateRange = new DatePeriod($start, $interval, $end->modify('+1 day'));

    foreach ($dateRange as $date) {
        $workEvents[] = [
            'event_date' => $date->format('Y-m-d'),
            'event_type' => 'work',
            'project_name' => $work['project_name'],
            'workers' => $work['workers']
        ];
    }
}

// Események összefűzése
$events = array_merge($projectEvents, $workEvents);

try {
    $db = Database::getInstance()->getConnection();

    // Lekérjük a csapattagokat ugyanúgy, mint a csapat.php-ben
    $stmt = $db->prepare("
        SELECT 
            u.*,
            GROUP_CONCAT(DISTINCT r.role_name) as roles,
            s.name as status_name,
            DATE_FORMAT(u.connect_date, '%Y. %m. %d.') as formatted_date
        FROM user u
        LEFT JOIN user_to_roles utr ON u.id = utr.user_id
        LEFT JOIN roles r ON utr.role_id = r.id
        LEFT JOIN status s ON u.current_status_id = s.id
        WHERE u.company_id = (
            SELECT company_id 
            FROM user 
            WHERE id = :user_id
        )
        GROUP BY u.id, s.name
        ORDER BY u.firstname
    ");

    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug információ
    error_log('Users found: ' . count($users));
    foreach ($users as $user) {
        error_log('User ' . $user['firstname'] . ' profile pic path: ' . $user['profile_pic']);
    }
} catch (PDOException $e) {
    $error = 'Adatbázis hiba: ' . $e->getMessage();
    error_log($e->getMessage());
}

// Debug információk
if (empty($users)) {
    error_log('No users found for user_id: ' . $_SESSION['user_id']);
}

require_once '../includes/layout/header.php';
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../assets/img/monitor.png">
    <title>Naptár - TechSolutions</title>
    <style>
        .calendar-container {
            max-width: 98%;
            margin: 1rem auto 1rem auto;
            padding: 0 1rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            grid-template-rows: auto repeat(6, minmax(80px, 1fr));
            gap: 4px;
            background: #eee;
            padding: 4px;
            border-radius: 10px;
            margin: 1rem;
            aspect-ratio: 7/5;
            height: calc(98vh - 220px);
            /* További 10px-el csökkentett magasság */
        }

        .calendar-header-cell {
            background: #2c3e50;
            color: white;
            padding: 0.8rem;
            text-align: center;
            font-weight: bold;
            font-size: 1.1rem;
            border-radius: 5px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .calendar-cell {
            background: white;
            position: relative;
            cursor: pointer;
            border-radius: 5px;
            transition: transform 0.2s, box-shadow 0.2s;
            padding: 0.8rem;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            min-height: 80px;
        }

        /* Új stílus az aktuális naphoz */
        .calendar-cell.today {
            border: 2px solid #3498db;
            /* Kék keret */
            background-color: rgba(52, 152, 219, 0.05);
            /* Halvány kék háttér */
        }

        .calendar-cell.today .date-number {
            color: #3498db;
            /* Kék szín a dátumnak */
            font-weight: bold;
        }

        .date-number {
            position: absolute;
            top: 5px;
            right: 8px;
            font-size: 1.1rem;
            color: #2c3e50;
            font-weight: bold;
        }

        .event {
            margin: 2px 0;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
            color: white;
            cursor: pointer;
            transition: transform 0.2s;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 25px;
        }

        .event.project {
            background-color: #4299e1;
            /* Kék - projektek */
            border-left: 4px solid #2b6cb0;
        }

        .event.work {
            background-color: #48bb78;
            /* Zöld - munkák */
            border-left: 4px solid #2f855a;
        }

        .event:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .calendar-cell {
            max-height: 150px;
            overflow-y: auto;
        }

        .calendar-cell::-webkit-scrollbar {
            width: 4px;
        }

        .calendar-cell::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .calendar-cell::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 2px;
        }

        /* Fejléc stílusok */
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            border-bottom: 1px solid #eee;
        }

        .month-nav {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        #currentMonth {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
            text-align: center;
            padding: 0 100px;
        }

        .nav-btn {
            background: #2c3e50;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.3rem;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }

        .nav-btn.prev {
            left: 2rem;
        }

        .nav-btn.next {
            right: 2rem;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(3px);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            /* Növelt szélesség 500px-ről 600px-re */
            max-height: 90vh;
            /* Növelt magasság 80vh-ról 90vh-ra */
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal h3 {
            margin: 0 0 1.2rem 0;
            color: #2c3e50;
            font-size: 1.3rem;
            /* Kisebb címsor */
            padding-right: 30px;
            /* Hely az X-nek */
        }

        .event-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            /* Kisebb gap */
        }

        .event-form select,
        .event-form textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .event-form textarea {
            min-height: 100px;
            resize: vertical;
        }

        .event-form select:focus,
        .event-form textarea:focus {
            border-color: #3498db;
            outline: none;
        }

        .event-form button {
            background: #2c3e50;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .event-form button:hover {
            background: #34495e;
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 18px;
            color: #000;
            /* Fekete szín */
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            /* Vastagabb X */
        }

        .close-modal:hover {
            opacity: 0.7;
        }

        .selected-date {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        /* Görgetősáv stílusa */
        .calendar-cell::-webkit-scrollbar {
            width: 4px;
        }

        .calendar-cell::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .calendar-cell::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 2px;
        }

        .calendar-cell::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Az üres cellák stílusa */
        .calendar-cell.empty {
            background: #f8f9fa;
            cursor: default;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
            /* Csökkentett margó */
        }

        .form-group label {
            font-weight: 500;
            color: #2c3e50;
        }

        .time-range {
            margin-top: 0.8rem;
        }

        .time-inputs {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .time-inputs input[type="time"] {
            padding: 0.5rem;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            width: 140px;
        }

        .time-inputs span {
            color: #666;
            font-weight: bold;
        }

        select {
            padding: 0.8rem;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            width: 100%;
            cursor: pointer;
        }

        select:focus {
            border-color: #3498db;
            outline: none;
        }

        /* Többszörös kiválasztás stílusa */
        #selectedUsers {
            width: 100%;
            min-height: 150px;
            padding: 8px;
            border: 2px solid #eee;
            border-radius: 8px;
            margin-bottom: 5px;
        }

        #selectedUsers option {
            padding: 8px;
            margin: 2px 0;
            border-radius: 4px;
        }

        #selectedUsers option:checked {
            background-color: #3498db;
            color: white;
        }

        .form-group small {
            color: #666;
            font-size: 0.8rem;
            margin-top: 5px;
            display: block;
        }

        .team-members-select {
            max-height: 280px;
            /* Csökkentett magasság, hogy több hely maradjon */
            overflow-y: auto;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 10px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .team-member-option input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .member-card {
            display: flex;
            align-items: center;
            padding: 8px;
            /* Csökkentett padding */
            border: 1px solid #eee;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 100%;
            background: white;
        }

        /* Kijelölés stílusa - csak a kártya háttere változik */
        .team-member-option input[type="checkbox"]:checked+.member-card {
            background-color: #e3f2fd;
            border: 1px solid #2196f3;
            box-shadow: 0 2px 8px rgba(33, 150, 243, 0.1);
        }

        /* Többi stílus marad ugyanaz */
        .member-avatar {
            position: relative;
            width: 35px;
            /* Kicsit kisebb avatar */
            height: 35px;
            margin-right: 10px;
            flex-shrink: 0;
            padding-bottom: 3px;
            padding-right: 3px;
        }

        .member-image {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .member-info {
            flex-grow: 1;
            overflow: hidden;
        }

        .member-name {
            font-weight: bold;
            margin-bottom: 2px;
            font-size: 0.85rem;
            /* Kisebb betűméret */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .member-status {
            font-size: 0.75em;
            /* Kisebb betűméret */
            color: #666;
            margin-bottom: 2px;
        }

        .member-role {
            font-size: 0.75em;
            /* Kisebb betűméret */
            color: #2196f3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .no-members {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .status-indicator {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            border: 2px solid white;
            background-color: #ccc;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Státusz színek */
        .status-indicator.elérhető {
            background-color: #2ecc71;
        }

        .status-indicator.munkában {
            background-color: #3498db;
        }

        .status-indicator.lefoglalt {
            background-color: #f1c40f;
        }

        .status-indicator.szabadság {
            background-color: #e67e22;
        }

        .status-indicator.betegállomány {
            background-color: #e74c3c;
        }

        .selection-info {
            position: relative;
            margin: 8px 0;
            /* Csökkentett margó */
            padding: 6px 35px 6px 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #e9ecef;
            min-height: 30px;
            /* Csökkentett magasság */
        }

        .close-help {
            position: absolute;
            top: 5px;
            /* Kicsit feljebb */
            right: 8px;
            background: none;
            border: none;
            font-size: 14px;
            /* Kisebb betűméret */
            color: #666;
            cursor: pointer;
            padding: 0;
            width: auto;
            /* Automatikus szélesség */
            height: auto;
            /* Automatikus magasság */
            display: inline;
            /* Inline megjelenítés */
            line-height: 1;
        }

        .close-help:hover {
            color: #333;
        }

        /* Info szöveg méretének csökkentése */
        .info-text p {
            margin: 0;
            font-size: 0.8rem;
            /* Kisebb betűméret */
            color: #495057;
            line-height: 1.2;
            /* Kisebb sorköz */
        }

        .info-icon {
            font-size: 0.9rem;
            /* Kisebb ikon */
            line-height: 1;
        }

        .hidden {
            display: none;
        }

        textarea[name="description"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            resize: vertical;
            min-height: 60px;
            /* Csökkentett minimum magasság */
            font-family: inherit;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        textarea[name="description"]:focus {
            border-color: #2196f3;
            outline: none;
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
        }

        .search-box {
            position: relative;
            margin-bottom: 10px;
        }

        .member-search-input {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.3s;
        }

        .member-search-input:focus {
            border-color: #2196f3;
            outline: none;
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
        }

        /* Keresési találat kiemelése */
        .team-member-option.hidden {
            display: none;
        }

        /* Esemény típusok színei */
        .event.work {
            background-color: #3498db;
            /* Kék - munkaidő */
        }

        .event.vacation {
            background-color: #e67e22;
            /* Narancssárga - szabadság */
        }

        .event.sick {
            background-color: #e74c3c;
            /* Piros - betegállomány */
        }

        .date-inputs {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .date-input-group {
            flex: 1;
        }

        .date-input-group label {
            display: block;
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
            color: #666;
        }

        .date-input-group input[type="date"] {
            width: 100%;
            padding: 0.5rem;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
        }

        .date-input-group input[type="date"]:focus {
            border-color: #2196f3;
            outline: none;
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
        }
    </style>
</head>

<body>
    <div class="calendar-container">
        <div class="calendar-header">
            <div class="month-nav">
                <button class="nav-btn prev" onclick="previousMonth()">←</button>
                <h2 id="currentMonth"></h2>
                <button class="nav-btn next" onclick="nextMonth()">→</button>
            </div>
        </div>

        <div class="calendar-grid">
            <div class="calendar-header-cell">Hét</div>
            <div class="calendar-header-cell">Kedd</div>
            <div class="calendar-header-cell">Sze</div>
            <div class="calendar-header-cell">Csüt</div>
            <div class="calendar-header-cell">Pén</div>
            <div class="calendar-header-cell">Szo</div>
            <div class="calendar-header-cell">Vas</div>
        </div>
    </div>

    <!-- Esemény hozzáadása modal -->
    <div id="eventModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h3>Munkaidő beállítása</h3>
            <div class="selected-date" id="selectedDateDisplay"></div>
            <form id="eventForm" class="event-form">
                <input type="hidden" id="selectedDate" name="date">

                <?php if ($_SESSION['user_role'] === 'Cég tulajdonos'): ?>
                    <div class="form-group">
                        <label for="selectedUsers">Csapattagok kiválasztása:</label>
                        <div class="search-box">
                            <input type="text"
                                id="memberSearch"
                                placeholder="Keresés név vagy szerepkör alapján..."
                                class="member-search-input">
                        </div>
                        <div class="team-members-select">
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <div class="team-member-option" data-search="<?php
                                                                                    echo htmlspecialchars(strtolower($user['firstname'] . ' ' .
                                                                                        $user['lastname'] . ' ' . $user['roles']));
                                                                                    ?>">
                                        <input type="checkbox"
                                            name="user_ids[]"
                                            value="<?php echo htmlspecialchars($user['id']); ?>"
                                            id="user_<?php echo $user['id']; ?>">
                                        <label for="user_<?php echo $user['id']; ?>" class="member-card">
                                            <div class="member-avatar">
                                                <img src="<?php
                                                            $profile_pic = $user['profile_pic'] ?? 'user.png';
                                                            echo file_exists('../uploads/profiles/' . $profile_pic)
                                                                ? '../uploads/profiles/' . $profile_pic
                                                                : '../assets/img/user.png';
                                                            ?>" alt="<?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>"
                                                    class="member-image">
                                                <?php
                                                // Debug
                                                error_log('Status name: ' . $user['status_name']);
                                                ?>
                                                <span class="status-indicator <?php echo mb_strtolower($user['status_name'], 'UTF-8'); ?>"></span>
                                            </div>
                                            <div class="member-info">
                                                <div class="member-name"><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></div>
                                                <div class="member-status"><?php echo htmlspecialchars($user['status_name']); ?></div>
                                                <div class="member-role"><?php echo htmlspecialchars($user['roles']); ?></div>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-members">Nincsenek elérhető csapattagok</div>
                            <?php endif; ?>
                        </div>
                        <div class="selection-info" id="selectionHelp">
                            <div class="info-icon">ℹ️</div>
                            <div class="info-text">
                                <p>A munkaidő beállításához kattintson a kiválasztani kívánt személyekre. Több személy is kiválasztható egyszerre.</p>
                                <p>A kiválasztott személyek kártyája kék háttérrel jelenik meg.</p>
                            </div>
                            <button class="close-help" onclick="hideSelectionHelp()">×</button>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="eventType">Esemény típusa:</label>
                    <select id="eventType" name="event_type" required>
                        <option value="vacation">Szabadság</option>
                        <option value="sick">Betegállomány</option>
                    </select>
                </div>

                <div class="form-group date-range" id="dateInputs">
                    <label>Időszak:</label>
                    <div class="date-inputs">
                        <div class="date-input-group">
                            <label for="startDate">Kezdő dátum:</label>
                            <input type="date" id="startDate" name="start_date" required>
                        </div>
                        <div class="date-input-group">
                            <label for="endDate">Befejező dátum:</label>
                            <input type="date" id="endDate" name="end_date" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="workDescription">Megjegyzés:</label>
                    <textarea id="workDescription" name="description" rows="2"
                        placeholder="Opcionális megjegyzés..."></textarea>
                </div>

                <button type="submit">Mentés</button>
            </form>
        </div>
    </div>

    <script>
        // Az events változó inicializálása a PHP-ból
        let events = <?php echo json_encode(array_merge($projectEvents, $workEvents)); ?>;

        // Inicializáljuk a currentDate-et a mai dátummal
        let currentDate = new Date();

        function initCalendar() {
            const grid = document.querySelector('.calendar-grid');
            const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);

            // Fejléc után töröljük a meglévő cellákat
            while (grid.children.length > 7) {
                grid.removeChild(grid.lastChild);
            }

            // Az első nap helyének kiszámítása (0 = vasárnap, 1 = hétfő, stb.)
            let firstDayOfWeek = firstDay.getDay();
            firstDayOfWeek = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;

            // Üres cellák hozzáadása a hónap első napja előtt
            for (let i = 0; i < firstDayOfWeek; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'calendar-cell empty';
                grid.appendChild(emptyCell);
            }

            // Mai dátum lekérése az összehasonlításhoz
            const today = new Date();
            const isCurrentMonth = today.getMonth() === currentDate.getMonth() &&
                today.getFullYear() === currentDate.getFullYear();

            // Naptár feltöltése
            for (let i = 1; i <= lastDay.getDate(); i++) {
                const cell = document.createElement('div');
                cell.className = 'calendar-cell';

                // Ha ez a mai nap, hozzáadjuk a today osztályt
                if (isCurrentMonth && today.getDate() === i) {
                    cell.classList.add('today');
                }

                cell.innerHTML = `<span class="date-number">${i}</span>`;

                // Események megjelenítése
                const currentDateStr = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                const dayEvents = events.filter(event => event.event_date === currentDateStr);

                dayEvents.forEach(event => {
                    const eventDiv = document.createElement('div');
                    eventDiv.className = `event ${event.event_type}`;

                    if (event.event_type === 'project') {
                        eventDiv.textContent = `${event.name} (${event.type_name})`;
                        eventDiv.title = `Projekt: ${event.name}\nTípus: ${event.type_name}`;
                    } else if (event.event_type === 'work') {
                        eventDiv.textContent = `${event.project_name} - ${event.workers}`;
                        eventDiv.title = `Munka: ${event.project_name}\nDolgozók: ${event.workers}`;
                    }

                    cell.appendChild(eventDiv);
                });

                cell.onclick = () => openModal(i);
                grid.appendChild(cell);
            }

            // Üres cellák hozzáadása a hónap utolsó napja után, hogy kitöltsük a 6 sort
            const totalCells = grid.children.length;
            const cellsNeeded = 7 * 7 - totalCells; // 7 oszlop * 6 sor + fejléc sor

            for (let i = 0; i < cellsNeeded; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'calendar-cell empty';
                grid.appendChild(emptyCell);
            }

            updateMonthDisplay();
        }

        function getEventTypeText(type) {
            const types = {
                'project': 'Projekt',
                'work': 'Munka',
                'vacation': 'Szabadság',
                'sick': 'Betegszabadság'
            };
            return types[type] || type;
        }

        function updateMonthDisplay() {
            const months = ['január', 'február', 'március', 'április', 'május', 'június',
                'július', 'augusztus', 'szeptember', 'október', 'november', 'december'
            ];
            document.getElementById('currentMonth').textContent =
                `${months[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
        }

        function previousMonth() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            initCalendar();
        }

        function nextMonth() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            initCalendar();
        }

        function openModal(day) {
            const modal = document.getElementById('eventModal');
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const dateDisplay = document.getElementById('selectedDateDisplay');

            // Dátum formázása
            const monthNames = ['január', 'február', 'március', 'április', 'május', 'június',
                'július', 'augusztus', 'szeptember', 'október', 'november', 'december'
            ];
            const formattedDate = `${currentDate.getFullYear()}. ${monthNames[currentDate.getMonth()]} ${day}.`;

            // Dátumok beállítása
            const selectedDate = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            startDateInput.value = selectedDate;
            endDateInput.value = selectedDate;

            // Minimum dátum beállítása
            startDateInput.min = selectedDate;
            endDateInput.min = selectedDate;

            // Dátum megjelenítése
            dateDisplay.textContent = `Kiválasztott dátum: ${formattedDate}`;

            // Modal megjelenítése
            modal.style.display = 'block';

            // Form resetelése
            document.getElementById('eventForm').reset();
            startDateInput.value = selectedDate;
        }

        function closeModal() {
            document.getElementById('eventModal').style.display = 'none';
        }

        // Dátum validáció
        document.getElementById('endDate').addEventListener('change', function() {
            const startDate = document.getElementById('startDate').value;
            const endDate = this.value;

            if (startDate && endDate && startDate > endDate) {
                alert('A befejező dátumnak későbbinek kell lennie, mint a kezdő dátum!');
                this.value = startDate;
            }
        });

        document.getElementById('startDate').addEventListener('change', function() {
            const endDate = document.getElementById('endDate');
            endDate.min = this.value;
            if (endDate.value && endDate.value < this.value) {
                endDate.value = this.value;
            }
        });

        // Módosítjuk az esemény mentését
        document.getElementById('eventForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            const selectedUsers = Array.from(document.querySelectorAll('input[name="user_ids[]"]:checked'))
                .map(checkbox => ({
                    id: checkbox.value,
                    name: checkbox.closest('.team-member-option').querySelector('.member-name').textContent
                }));

            formData.delete('user_ids[]');
            selectedUsers.forEach(user => formData.append('user_ids[]', user.id));

            // Dátum intervallum feldolgozása
            const startDate = new Date(formData.get('start_date'));
            const endDate = new Date(formData.get('end_date'));
            const eventType = formData.get('event_type');

            // Minden napra létrehozunk egy eseményt a megadott intervallumon belül
            for (let date = new Date(startDate); date <= endDate; date.setDate(date.getDate() + 1)) {
                const eventDate = date.toISOString().split('T')[0];
                events.push({
                    event_date: eventDate,
                    event_type: eventType,
                    user_name: selectedUsers.map(u => u.name).join(', '),
                    description: formData.get('description')
                });
            }

            // AJAX kérés az esemény mentéséhez
            fetch('save_calendar_event.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        initCalendar();
                        showSuccessMessage('Esemény sikeresen mentve!');
                    } else {
                        alert(data.message || 'Hiba történt az esemény mentése során!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Hiba történt az esemény mentése során!');
                });
        };

        function showSuccessMessage(message) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'success-message';
            messageDiv.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(messageDiv);

            setTimeout(() => {
                messageDiv.remove();
            }, 3000);
        }

        // Naptár inicializálása az oldal betöltésekor
        initCalendar();

        // Modal bezárása kattintásra a háttéren
        window.onclick = function(event) {
            const modal = document.getElementById('eventModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Segítő szöveg elrejtése és mentése localStorage-ba
        function hideSelectionHelp() {
            const helpBox = document.getElementById('selectionHelp');
            helpBox.classList.add('hidden');
            localStorage.setItem('selectionHelpHidden', 'true');
        }

        // Oldal betöltésekor ellenőrizzük, hogy el kell-e rejteni a segítő szöveget
        document.addEventListener('DOMContentLoaded', function() {
            const helpBox = document.getElementById('selectionHelp');
            if (localStorage.getItem('selectionHelpHidden') === 'true') {
                helpBox.classList.add('hidden');
            }
        });

        // Keresés funkció hozzáadása
        document.getElementById('memberSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const memberCards = document.querySelectorAll('.team-member-option');

            memberCards.forEach(card => {
                const searchData = card.dataset.search;
                if (searchData.includes(searchTerm)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });

            // "Nincs találat" üzenet kezelése
            const visibleCards = document.querySelectorAll('.team-member-option:not(.hidden)');
            const noResultsMsg = document.querySelector('.no-results-message');

            if (visibleCards.length === 0) {
                if (!noResultsMsg) {
                    const msg = document.createElement('div');
                    msg.className = 'no-results-message';
                    msg.style.textAlign = 'center';
                    msg.style.padding = '20px';
                    msg.style.color = '#666';
                    msg.textContent = 'Nincs találat a keresésre';
                    document.querySelector('.team-members-select').appendChild(msg);
                }
            } else {
                if (noResultsMsg) {
                    noResultsMsg.remove();
                }
            }
        });

        // Esemény típus változásának kezelése
        document.getElementById('eventType').addEventListener('change', function() {
            const timeInputs = document.getElementById('timeInputs');
            const startTime = document.getElementById('startTime');
            const endTime = document.getElementById('endTime');

            if (this.value === 'vacation' || this.value === 'sick') {
                // Szabadság és betegállomány esetén automatikusan egész nap
                startTime.value = '00:00';
                endTime.value = '23:59';
                timeInputs.style.display = 'none';
            } else {
                timeInputs.style.display = 'block';
                startTime.value = '';
                endTime.value = '';
            }
        });
    </script>
</body>

</html>