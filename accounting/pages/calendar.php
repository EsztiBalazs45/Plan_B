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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/hu.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f9fafb, #334155);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            color: #374151;
            padding: 2rem;
        }
        .content-wrapper {
            max-width: 1400px;
            margin: 0 auto;
        }
        .calendar-container {
            background: #ffffff;
            border-radius: 25px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            height: calc(100vh - 120px);
            animation: fadeInUp 0.5s ease-out;
            border: 1px solid #e5e7eb;
        }
        #calendar {
            height: 100%;
        }
        /* FullCalendar testreszabása */
        .fc-header-toolbar {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            padding: 1rem;
            border-radius: 15px 15px 0 0;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .fc-toolbar-title {
            color: #1e3a8a;
            font-weight: 600;
            font-size: 1.5rem;
        }
        .fc-button {
            border-radius: 10px !important;
            padding: 0.5rem 1rem !important;
            font-size: 0.9rem !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #60a5fa !important;
            border: none !important;
            color: #ffffff !important;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        .fc-button:hover {
            background: #3b82f6 !important;
            transform: scale(1.05);
        }
        .fc-button-primary {
            background: #10b981 !important;
        }
        .fc-button-primary:hover {
            background: #059669 !important;
        }
        .fc-customAddButton-button {
            margin-left: 0.5rem;
            background: #f97316 !important;
        }
        .fc-customAddButton-button:hover {
            background: #ea580c !important;
        }
        /* Idősávok és napok */
        .fc-timegrid-slot {
            height: 45px !important;
            background: #f9fafb;
            border-color: #e5e7eb;
        }
        .fc-col-header-cell {
            background: #dbeafe;
            color: #1e3a8a;
            font-weight: 600;
            padding: 0.75rem;
            border-bottom: 2px solid #bfdbfe;
        }
        .fc-daygrid-day-number {
            color: #6b7280;
            font-size: 1rem;
            font-weight: 400;
        }
        .fc-daygrid-day {
            background: #ffffff;
            transition: background 0.3s ease;
        }
        .fc-daygrid-day:hover {
            background: #f1f5f9;
        }
        /* Események testreszabása */
        .fc-event {
            border-radius: 10px;
            padding: 0.5rem;
            font-size: 0.9rem;
            font-weight: 400;
            background: #f97316;
            border: none;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .fc-event:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .fc-event-time {
            font-weight: 600;
            color: #ffffff;
        }
        .fc-event-title {
            color: #fefcbf;
            white-space: normal;
        }
        /* Reszponzivitás */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            .calendar-container {
                padding: 1rem;
                height: calc(100vh - 100px);
            }
            .fc-header-toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            .fc-toolbar-chunk {
                margin-bottom: 0.5rem;
            }
            .fc-button {
                padding: 0.4rem 0.8rem !important;
                font-size: 0.85rem !important;
            }
            .fc-timegrid-slot {
                height: 35px !important;
            }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </div>

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
                validRange: {
                    start: new Date()
                },
                eventClick: function(info) {
                    window.location.href = 'appointments.php?action=edit&id=' + info.event.id;
                },
                eventContent: function(arg) {
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