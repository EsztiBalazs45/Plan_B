<?php
require_once '../includes/header.php';
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Ügyfelek lekérdezése (csak a kompatibilitás miatt marad, de itt nem használjuk)
$stmt = $conn->prepare("SELECT id, CompanyName FROM clients WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Naptár</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/hu.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="content-wrapper">
        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </div>

    <style>
        .content-wrapper {
            padding: 1rem;
        }
        .calendar-container {
            max-width: 98%;
            margin: 1rem auto;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            height: calc(98vh - 100px);
        }
        #calendar {
            height: 100%;
        }
        .fc-timegrid-slot {
            height: 30px !important; /* 30 perces idősávok */
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'timeGridWeek', // Heti nézet alapértelmezett
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                locale: 'hu',
                firstDay: 1,
                slotMinTime: '07:00:00', // Reggel 7-től
                slotMaxTime: '16:00:00', // Délután 4-ig
                slotDuration: '00:30:00', // 30 perces idősávok
                height: '100%',
                selectable: false, // Kiválasztás tiltása
                editable: false, // Szerkeszthetőség tiltása
                events: 'get_events.php',
                validRange: {
                    start: new Date() // Múltbeli napok tiltása (csak vizuálisan)
                },
                eventClick: function(info) {
                    // Átirányítás az appointments.php szerkesztő felületére
                    window.location.href = 'appointments.php?action=edit&id=' + info.event.id;
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>

<?php require_once '../includes/footer.php'; ?>