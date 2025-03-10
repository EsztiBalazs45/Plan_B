<?php
require_once '../includes/header.php';
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Ügyfelek lekérdezése (csak kompatibilitás miatt)
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
        /* Alapvető elrendezés */
        .content-wrapper {
            padding: 2rem;
            background: #f4f6f9; /* Halvány háttér a kontraszt növelésére */
        }
        .calendar-container {
            max-width: 1200px; /* Fix szélesség a jobb olvashatóság érdekében */
            margin: 0 auto;
            padding: 25px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); /* Finomabb árnyék */
            height: calc(100vh - 120px); /* Magasság optimalizálása */
        }
        #calendar {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Modernebb betűtípus */
        }

        /* FullCalendar testreszabása */
        .fc-header-toolbar {
            padding: 10px 20px;
            background: #ffffff;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 15px;
        }
        .fc-button {
            border-radius: 8px !important;
            padding: 8px 16px !important;
            font-size: 14px !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
        }
        .fc-button-primary {
            background-color: #007bff !important;
            border-color: #007bff !important;
        }
        .fc-button-primary:hover {
            background-color: #0056b3 !important;
            border-color: #0056b3 !important;
        }
        .fc-customAddButton-button {
            margin-left: 10px;
        }

        /* Idősávok és napok */
        .fc-timegrid-slot {
            height: 40px !important; /* Magasabb idősávok a jobb átláthatóságért */
            border-color: #e9ecef;
        }
        .fc-col-header-cell {
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
            padding: 10px;
            border-bottom: 2px solid #dee2e6;
        }
        .fc-daygrid-day-number {
            font-size: 14px;
            color: #495057;
        }

        /* Események testreszabása */
        .fc-event {
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        .fc-event:hover {
            transform: translateY(-2px); /* Kis kiemelés hovernél */
        }
        .fc-event-time {
            font-weight: 600;
        }
        .fc-event-title {
            white-space: normal; /* Szöveg tördelése, ha hosszú */
        }

        /* Reszponzivitás */
        @media (max-width: 768px) {
            .calendar-container {
                padding: 15px;
                height: calc(100vh - 100px);
            }
            .fc-header-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }
            .fc-toolbar-chunk {
                margin-bottom: 10px;
            }
            .fc-button {
                padding: 6px 12px !important;
                font-size: 12px !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay customAddButton'
                },
                customButtons: {
                    customAddButton: {
                        text: 'Időpont foglalás',
                        click: function() {
                            window.location.href = 'appointments.php?action=new';
                        }
                    }
                },
                locale: 'hu',
                firstDay: 1,
                slotMinTime: '07:00:00',
                slotMaxTime: '16:00:00',
                slotDuration: '00:30:00',
                height: '100%',
                selectable: false,
                editable: false,
                events: 'get_events.php',
                validRange: { start: new Date() },
                eventClick: function(info) {
                    window.location.href = 'appointments.php?action=edit&id=' + info.event.id;
                },
                eventContent: function(arg) {
                    // Esemény tartalmának testreszabása
                    return {
                        html: `
                            <div>
                                <strong>${arg.timeText}</strong> ${arg.event.title}
                            </div>
                        `
                    };
                }
            });
            calendar.render();
            document.querySelector('.fc-customAddButton-button').classList.add('btn', 'btn-primary');
        });
    </script>
</body>
</html>

<?php require_once '../includes/footer.php'; ?>