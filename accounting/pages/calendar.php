<?php
require_once '../includes/header.php';
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Ügyfelek lekérdezése
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

    <!-- Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Időpont kezelése</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="eventForm">
                    <div class="modal-body">
                        <input type="hidden" id="eventId" name="eventId">
                        <div class="mb-3">
                            <label for="eventTitle" class="form-label">Cím</label>
                            <input type="text" class="form-control" id="eventTitle" name="eventTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="clientId" class="form-label">Ügyfél</label>
                            <select class="form-select" id="clientId" name="clientId" required>
                                <option value="">Válasszon ügyfelet...</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['CompanyName']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="eventStart" class="form-label">Kezdés</label>
                            <input type="datetime-local" class="form-control" id="eventStart" name="eventStart" required>
                        </div>
                        <div class="mb-3">
                            <label for="eventEnd" class="form-label">Befejezés</label>
                            <input type="datetime-local" class="form-control" id="eventEnd" name="eventEnd" required>
                        </div>
                        <div class="mb-3">
                            <label for="eventDescription" class="form-label">Leírás</label>
                            <textarea class="form-control" id="eventDescription" name="eventDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="eventStatus" class="form-label">Státusz</label>
                            <select class="form-select" id="eventStatus" name="eventStatus">
                                <option value="pending">Függőben</option>
                                <option value="confirmed">Megerősítve</option>
                                <option value="canceled">Lemondva</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="deleteEvent">Törlés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezárás</button>
                        <button type="submit" class="btn btn-primary">Mentés</button>
                    </div>
                </form>
            </div>
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
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                locale: 'hu',
                firstDay: 1,
                height: '100%',
                selectable: true,
                editable: true,
                events: 'get_events.php',
                validRange: {
                    start: new Date() // Múltbeli napok tiltása
                },
                select: function(info) {
                    var now = new Date();
                    if (info.start < now) {
                        alert('Nem foglalhatsz múltbeli időpontot!');
                        return;
                    }
                    $('#eventId').val('');
                    $('#eventTitle').val('');
                    $('#clientId').val('');
                    $('#eventStart').val(info.startStr.slice(0, 16));
                    $('#eventEnd').val(info.endStr.slice(0, 16));
                    $('#eventDescription').val('');
                    $('#eventStatus').val('pending');
                    $('#eventModal').modal('show');
                },
                eventClick: function(info) {
                    $('#eventId').val(info.event.id);
                    $('#eventTitle').val(info.event.title);
                    $('#clientId').val(info.event.extendedProps.client_id);
                    $('#eventStart').val(info.event.start.toISOString().slice(0, 16));
                    $('#eventEnd').val(info.event.end.toISOString().slice(0, 16));
                    $('#eventDescription').val(info.event.extendedProps.description);
                    $('#eventStatus').val(info.event.extendedProps.status);
                    $('#eventModal').modal('show');
                },
                eventDrop: function(info) {
                    updateEvent(info.event);
                },
                eventResize: function(info) {
                    updateEvent(info.event);
                }
            });
            calendar.render();

            $('#eventForm').on('submit', function(e) {
                e.preventDefault();
                const eventId = $('#eventId').val();
                const url = eventId ? 'update_event.php' : 'add_event.php';
                const start = $('#eventStart').val();
                const now = new Date().toISOString().slice(0, 16);

                if (start < now) {
                    alert('Nem foglalhatsz múltbeli időpontot!');
                    return;
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: JSON.stringify({
                        id: eventId,
                        title: $('#eventTitle').val(),
                        clientId: $('#clientId').val(),
                        start: start,
                        end: $('#eventEnd').val(),
                        description: $('#eventDescription').val(),
                        status: $('#eventStatus').val()
                    }),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.success) {
                            if (!eventId) {
                                // Új esemény esetén adjuk hozzá a naptárhoz
                                calendar.addEvent(response.event);
                            }
                            calendar.refetchEvents(); // Frissítjük az összes eseményt
                            $('#eventModal').modal('hide');
                            alert(response.message);
                        } else {
                            alert('Hiba: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Hiba történt a művelet során');
                    }
                });
            });
            $('#deleteEvent').on('click', function() {
                if (!confirm('Biztosan törlöd?')) return;
                const eventId = $('#eventId').val();
                if (!eventId) return;

                $.ajax({
                    url: 'delete_event.php',
                    method: 'POST',
                    data: JSON.stringify({
                        id: eventId
                    }),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.success) {
                            calendar.refetchEvents();
                            $('#eventModal').modal('hide');
                            alert(response.message);
                        } else {
                            alert('Hiba: ' + response.message);
                        }
                    }
                });
            });

            function updateEvent(event) {
                const now = new Date();
                if (event.start < now) {
                    alert('Nem mozgathatsz múltbeli időpontot!');
                    event.revert();
                    return;
                }

                $.ajax({
                    url: 'update_event.php',
                    method: 'POST',
                    data: JSON.stringify({
                        id: event.id,
                        title: event.title,
                        clientId: event.extendedProps.client_id,
                        start: event.start.toISOString().slice(0, 16),
                        end: event.end.toISOString().slice(0, 16),
                        description: event.extendedProps.description,
                        status: event.extendedProps.status
                    }),
                    contentType: 'application/json',
                    success: function(response) {
                        if (!response.success) {
                            event.revert();
                            alert('Hiba: ' + response.message);
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>

<?php require_once '../includes/footer.php'; ?>