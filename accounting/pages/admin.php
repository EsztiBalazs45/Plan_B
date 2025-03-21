<?php
require_once '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="hu">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Felület</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
</head>


<style>
    body {
        background: linear-gradient(135deg, #e0e7ff, #1e293b);
        font-family: 'Poppins', sans-serif;
        min-height: 100vh;
        padding: 2rem;
        color: #1e293b;
    }

    .navbar-custom {
        background: linear-gradient(135deg, #1e40af, #60a5fa);
        border-radius: 30px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        padding: 1.2rem;
        transition: all 0.3s ease;
    }

    .navbar-custom .navbar-brand,
    .navbar-custom .nav-link {
        color: #ffffff;
        font-weight: 600;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        transition: color 0.3s ease, transform 0.3s ease;
    }

    .navbar-custom .nav-link:hover,
    .navbar-custom .nav-link.active {
        color: #dbeafe;
        transform: scale(1.05);
    }

    .content-wrapper {
        max-width: 1400px;
        margin: 0 auto;
    }

    .card {
        border: none;
        border-radius: 25px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        background: #ffffff;
        animation: fadeInUp 0.5s ease-out;
        overflow: hidden;
    }

    .card-header {
        background: linear-gradient(135deg, #93c5fd, #3b82f6);
        color: #ffffff;
        border-radius: 25px 25px 0 0;
        padding: 1.8rem;
        font-weight: 600;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .dashboard-card {
        border-radius: 25px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: linear-gradient(135deg, #ffffff, #f8fafc);
    }

    .dashboard-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .dashboard-card i {
        color: #3b82f6;
        font-size: 2.5rem;
        margin-bottom: 0.8rem;
        transition: transform 0.3s ease;
    }

    .dashboard-card:hover i {
        transform: scale(1.1);
    }

    .table {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .table thead {
        background: #bfdbfe;
        color: #1e3a8a;
    }

    .table th,
    .table td {
        padding: 1.2rem;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background: #f1f5f9;
    }

    .subscription-card {
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        margin-bottom: 2rem;
        background: linear-gradient(135deg, #ffffff, #f1f5f9);
        border: 1px solid #e5e7eb;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .subscription-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
    }

    .subscription-card h6 {
        color: #1e40af;
        font-weight: 600;
        margin-bottom: 0.8rem;
    }

    .subscription-card p {
        margin: 0.6rem 0;
        color: #374151;
        font-size: 0.95rem;
    }

    .subscription-card strong {
        color: #1e293b;
        font-weight: 600;
    }

    .btn {
        border-radius: 50px;
        padding: 0.7rem 2rem;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }

    .btn-primary {
        background: #3b82f6;
        border: none;
    }

    .btn-primary:hover {
        background: #1e40af;
        transform: scale(1.05);
    }

    .btn-success {
        background: #10b981;
        border: none;
    }

    .btn-success:hover {
        background: #047857;
        transform: scale(1.05);
    }

    .btn-danger {
        background: #ef4444;
        border: none;
    }

    .btn-danger:hover {
        background: #b91c1c;
        transform: scale(1.05);
    }

    .search-bar {
        border-radius: 50px;
        padding: 0.8rem 1.8rem;
        border: 1px solid #d1d5db;
        width: 100%;
        max-width: 450px;
        margin-bottom: 2rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .search-bar:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 8px rgba(59, 130, 246, 0.4);
        outline: none;
    }

    .pagination .page-link {
        border-radius: 50px;
        margin: 0 0.3rem;
        color: #3b82f6;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .pagination .page-link:hover {
        background: #dbeafe;
        color: #1e3a8a;
        transform: scale(1.05);
    }

    .pagination .page-item.active .page-link {
        background: #3b82f6;
        color: #ffffff;
        border-color: #3b82f6;
    }

    .alert {
        border-radius: 15px;
        margin-top: 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
    }

    .status-active {
        color: #059669;
        font-weight: 600;
    }

    .status-expired {
        color: #d97706;
        font-weight: 600;
    }

    .status-canceled {
        color: #dc2626;
        font-weight: 600;
    }

    #calendar {
        max-width: 1500px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 2rem;
    }

    .fc-toolbar {
        background: linear-gradient(135deg, #93c5fd, #3b82f6);
        color: #ffffff;
        border-radius: 15px;
        padding: 1rem;
        margin-bottom: 2rem;
    }

    .fc-button {
        background: #1e40af !important;
        border: none !important;
        border-radius: 50px !important;
        padding: 0.7rem 1.8rem !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
        font-size: 1rem !important;
    }

    .fc-button:hover {
        background: #2563eb !important;
        transform: scale(1.05) !important;
    }

    .fc-button.fc-button-active {
        background: #10b981 !important;
    }

    .fc-event {
        background: linear-gradient(135deg, #60a5fa, #3b82f6);
        border: none;
        border-radius: 12px;
        padding: 0.8rem;
        color: #ffffff;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s ease;
        font-size: 1.2rem;
        line-height: 1.5;
        overflow: hidden;
        white-space: normal;
        word-wrap: break-word;
    }

    .fc-event:hover {
        transform: scale(1.03);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .fc-timegrid-slot {
        height: 2.5rem !important;
        /* Nagyobb slot-magasság a heti nézetben */
    }

    .fc-daygrid-day {
        height: 150px !important;
        /* Nagyobb napi cellák a havi nézetben */
    }

    .fc-timegrid-event {
        min-height: 2.5rem !important;
        /* Minimum magasság az eseményeknek */
    }

    @media (max-width: 768px) {
        body {
            padding: 1rem;
        }

        .navbar-custom {
            padding: 0.8rem;
        }

        .dashboard-card {
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }

        .subscription-card {
            padding: 1.5rem;
        }

        .btn {
            padding: 0.6rem 1.5rem;
        }

        .search-bar {
            max-width: 100%;
        }

        #calendar {
            padding: 1rem;
        }

        .fc-event {
            font-size: 1rem;
            padding: 0.6rem;
        }

        .fc-timegrid-slot {
            height: 2rem !important;
        }

        .fc-daygrid-day {
            height: 120px !important;
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>


<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="#" data-tab="users">Felhasználók</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" data-tab="clients">Ügyfelek</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" data-tab="appointments">Időpontok</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" data-tab="calendar">Naptár</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" data-tab="subscriptions">Előfizetések</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="content-wrapper">
        <div id="message" class="alert" style="display: none;"></div>

        <div class="row mt-4 mb-5" id="stats"></div>

        <input type="text" class="search-bar" id="searchInput" placeholder="Keresés név vagy email alapján...">

        <div class="card" id="content-card">
            <div class="card-header">
                <h5 id="tab-title"></h5>
            </div>
            <div class="card-body" id="content-body"></div>
        </div>
    </div>

    <div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClientModalLabel">Ügyfél szerkesztése</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editClientForm">
                        <input type="hidden" id="edit_client_id" name="client_id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Név</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Mentés</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let currentTab = 'users';
            let userPage = 1;
            let appointmentPage = 1;
            let sort = 'name_desc';

            function showMessage(message, type) {
                $('#message').text(message).removeClass().addClass(`alert alert-${type}`).show();
                setTimeout(() => $('#message').hide(), 5000);
            }

            function loadStats() {
                $.getJSON('../api/admin_api.php?tab=stats', function(stats) {
                    $('#stats').html(`
                <div class="col-md-3"><div class="dashboard-card"><i class="fas fa-users"></i><h3>${stats.total_users}</h3><p>Felhasználók</p></div></div>
                <div class="col-md-3"><div class="dashboard-card"><i class="fas fa-building"></i><h3>${stats.total_clients}</h3><p>Ügyfelek</p></div></div>
                <div class="col-md-3"><div class="dashboard-card"><i class="fas fa-calendar-check"></i><h3>${stats.total_appointments}</h3><p>Időpontok</p></div></div>
                <div class="col-md-3"><div class="dashboard-card"><i class="fas fa-money-bill"></i><h3>${stats.total_subscriptions}</h3><p>Aktív előfizetések</p></div></div>
            `);
                });
            }

            function loadContent() {
                $('#tab-title').text(currentTab === 'users' ? 'Felhasználók kezelése' :
                    currentTab === 'clients' ? 'Ügyfelek kezelése' :
                    currentTab === 'appointments' ? 'Időpontok kezelése' :
                    currentTab === 'subscriptions' ? 'Előfizetések kezelése' : 'Naptár');
                if (currentTab === 'users') {
                    $.getJSON(`../api/admin_api.php?tab=users&user_page=${userPage}&sort=${sort}`, function(data) {
                        let table = `<div class="table-responsive"><table class="table table-hover"><thead><tr>
                    <th><a href="#" class="sort-link" data-sort="${sort === 'name_asc' ? 'name_desc' : 'name_asc'}">Név ${sort === 'name_asc' ? '↑' : '↓'}</a></th>
                    <th>Email</th><th>Szerepkör</th><th>Műveletek</th></tr></thead><tbody>`;
                        data.users.forEach(user => {
                            table += `<tr data-name="${user.name.toLowerCase()}" data-email="${user.email.toLowerCase()}">
                        <td>${user.name}</td><td>${user.email}</td><td>
                        ${user.id === <?php echo $_SESSION['user_id']; ?> ? 
                            `<span class="role-${user.role}">${user.role === 'admin' ? 'Adminisztrátor' : 'Felhasználó'}</span>` : 
                            `<select class="form-select form-select-sm update-role" data-id="${user.id}">
                                <option value="user" ${user.role === 'user' ? 'selected' : ''}>Felhasználó</option>
                                <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Adminisztrátor</option>
                            </select>`}
                        </td><td>
                        ${user.id !== <?php echo $_SESSION['user_id']; ?> ? 
                            `<button class="btn btn-danger btn-sm delete-user" data-id="${user.id}"><i class="fas fa-trash"></i></button>` : ''}
                        </td></tr>`;
                        });
                        table += `</tbody></table></div>`;
                        let pagination = `<nav><ul class="pagination justify-content-center mt-3">`;
                        for (let i = 1; i <= data.total_pages; i++) {
                            pagination += `<li class="page-item ${userPage === i ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                        }
                        pagination += `</ul></nav>`;
                        $('#content-body').html(table + pagination);
                    });
                } else if (currentTab === 'clients') {
                    $.getJSON('../api/admin_api.php?tab=clients', function(clients) {
                        let table = `<div class="table-responsive"><table class="table table-hover"><thead><tr>
                    <th>Név</th><th>Felhasználó</th><th>Műveletek</th></tr></thead><tbody>`;
                        clients.forEach(client => {
                            table += `<tr data-name="${client.CompanyName.toLowerCase()}" data-user="${(client.user_name || '').toLowerCase()}">
                        <td>${client.CompanyName}</td><td>${client.user_name || 'Nincs hozzárendelve'}</td>
                        <td><button class="btn btn-primary btn-sm edit-client" data-id="${client.id}" data-name="${client.CompanyName}"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger btn-sm delete-client" data-id="${client.id}"><i class="fas fa-trash"></i></button></td></tr>`;
                        });
                        table += `</tbody></table></div>`;
                        $('#content-body').html(table);
                    });
                } else if (currentTab === 'appointments') {
                    $.getJSON(`../api/admin_api.php?tab=appointments&appointment_page=${appointmentPage}`, function(data) {
                        let content = `<div id="appointmentList">`;
                        data.appointments.forEach(appointment => {
                            content += `<div class="subscription-card" data-title="${appointment.title}" data-user="${appointment.user_name || ''}" data-client="${appointment.CompanyName || ''}" data-description="${appointment.description || ''}">
                        <h6><strong>Címke:</strong> ${appointment.title}</h6>
                        <p><strong>Foglalta:</strong> ${appointment.user_name || 'Nincs hozzárendelve'}</p>
                        <p><strong>Ügyfél:</strong> ${appointment.CompanyName || 'Nincs megadva'}</p>
                        <p><strong>Időpont:</strong> ${new Date(appointment.start).toLocaleString('hu-HU')} - ${new Date(appointment.end).toLocaleString('hu-HU', {hour: '2-digit', minute: '2-digit'})}</p>
                        <p><strong>Leírás:</strong> ${appointment.description || 'Nincs leírás'}</p>
                        <p><strong>Státusz:</strong> <span class="status-${appointment.status}">${appointment.status === 'pending' ? 'Függőben' : (appointment.status === 'confirmed' ? 'Megerősítve' : 'Lemondva')}</span></p>
                        ${appointment.status === 'pending' ? `
                            <button class="btn btn-success btn-sm update-status" data-id="${appointment.id}" data-status="confirmed"><i class="fas fa-check"></i> Megerősítés</button>
                            <button class="btn btn-danger btn-sm update-status" data-id="${appointment.id}" data-status="canceled"><i class="fas fa-times"></i> Lemondás</button>` : ''}
                        <button class="btn btn-danger btn-sm delete-appointment" data-id="${appointment.id}"><i class="fas fa-trash"></i> Törlés</button>
                    </div>`;
                        });
                        content += `</div>`;
                        let pagination = `<nav><ul class="pagination justify-content-center mt-3">`;
                        for (let i = 1; i <= data.total_pages; i++) {
                            pagination += `<li class="page-item ${appointmentPage === i ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                        }
                        pagination += `</ul></nav>`;
                        $('#content-body').html(content + pagination);
                    });
                } else if (currentTab === 'subscriptions') {
                    $.getJSON('../api/admin_api.php?tab=subscriptions', function(subscriptions) {
                        let content = `<div id="subscriptionList">`;
                        subscriptions.forEach(subscription => {
                            content += `<div class="subscription-card" data-client="${subscription.client_name || ''}" data-user="${subscription.user_name || ''}" data-service="${subscription.service_name}">
                        <h6><strong>Ügyfél:</strong> ${subscription.client_name || 'Nincs megadva'}</h6>
                        <p><strong>Felhasználó:</strong> ${subscription.user_name || 'Nincs hozzárendelve'}</p>
                        <p><strong>Előfizetés típusa:</strong> ${subscription.service_name}</p>
                        <p><strong>Státusz:</strong> <span class="status-${subscription.status}">${subscription.status === 'active' ? 'Aktív' : (subscription.status === 'expired' ? 'Lejárt' : 'Lemondva')}</span></p>
                    </div>`;
                        });
                        content += `</div>`;
                        $('#content-body').html(content);
                    });
                } else if (currentTab === 'calendar') {
                    $('#content-body').html('<div id="calendar"></div>');
                    $.getJSON('../api/admin_api.php?tab=calendar', function(events) {
                        var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                            initialView: 'timeGridWeek',
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            events: events,
                            eventTimeFormat: {
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: false
                            },
                            slotMinTime: '07:00:00',
                            slotMaxTime: '17:00:00',
                            allDaySlot: false,
                            slotDuration: '00:30:00',
                            slotLabelInterval: '01:00:00',
                            slotHeight: 50,
                            height: 'auto',
                            expandRows: true,
                            eventMinHeight: 50,
                            eventContent: function(arg) {
                                var startTime = arg.event.start.toLocaleTimeString('hu-HU', {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                                var endTime = arg.event.end ? arg.event.end.toLocaleTimeString('hu-HU', {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) : '';
                                return {
                                    html: `<div class="fc-event-main" style="padding: 5px;"><strong style="font-size: 1.2rem;">${arg.event.extendedProps.user_name}</strong><br><small style="font-size: 1rem;">${startTime} - ${endTime}</small></div>`
                                };
                            },
                            eventDidMount: function(info) {
                                var startTime = info.event.start.toLocaleTimeString('hu-HU', {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                                var endTime = info.event.end ? info.event.end.toLocaleTimeString('hu-HU', {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) : '';
                                info.el.setAttribute('title', `Foglalta: ${info.event.extendedProps.user_name}\nMikor: ${startTime} - ${endTime}`);
                            }
                        });
                        calendar.render();
                    });
                }
            }

            loadStats();
            loadContent();

            $('.navbar-nav a').click(function(e) {
                e.preventDefault();
                currentTab = $(this).data('tab');
                userPage = 1;
                appointmentPage = 1;
                $('.navbar-nav a').removeClass('active');
                $(this).addClass('active');
                loadContent();
            });

            $(document).on('click', '.sort-link', function(e) {
                e.preventDefault();
                sort = $(this).data('sort');
                loadContent();
            });

            $(document).on('click', '.pagination .page-link', function(e) {
                e.preventDefault();
                if (currentTab === 'users') {
                    userPage = $(this).data('page');
                } else if (currentTab === 'appointments') {
                    appointmentPage = $(this).data('page');
                }
                loadContent();
            });

            $(document).on('change', '.update-role', function() {
                let userId = $(this).data('id');
                let newRole = $(this).val();
                $.ajax({
                    url: '../api/admin_api.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        update_user_role: true,
                        user_id: userId,
                        new_role: newRole
                    }),
                    success: function(response) {
                        showMessage(response.message, 'success');
                        loadContent();
                    },
                    error: function(xhr) {
                        showMessage(xhr.responseJSON.error, 'danger');
                    }
                });
            });

            $(document).on('click', '.delete-user', function() {
                if (confirm('Biztosan törli ezt a felhasználót?')) {
                    let userId = $(this).data('id');
                    $.ajax({
                        url: '../api/admin_api.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            delete_user: true,
                            user_id: userId
                        }),
                        success: function(response) {
                            showMessage(response.message, 'success');
                            loadContent();
                        },
                        error: function(xhr) {
                            showMessage(xhr.responseJSON.error, 'danger');
                        }
                    });
                }
            });

            $(document).on('click', '.edit-client', function() {
                $('#edit_client_id').val($(this).data('id'));
                $('#edit_name').val($(this).data('name'));
                $('#editClientModal').modal('show');
            });

            $('#editClientForm').submit(function(e) {
                e.preventDefault();
                let data = {
                    edit_client: true,
                    client_id: $('#edit_client_id').val(),
                    name: $('#edit_name').val()
                };
                $.ajax({
                    url: '../api/admin_api.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    success: function(response) {
                        showMessage(response.message, 'success');
                        $('#editClientModal').modal('hide');
                        loadContent();
                    },
                    error: function(xhr) {
                        showMessage(xhr.responseJSON.error, 'danger');
                    }
                });
            });

            $(document).on('click', '.delete-client', function() {
                if (confirm('Biztosan törli ezt az ügyfelet?')) {
                    let clientId = $(this).data('id');
                    $.ajax({
                        url: '../api/admin_api.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            delete_client: true,
                            client_id: clientId
                        }),
                        success: function(response) {
                            showMessage(response.message, 'success');
                            loadContent();
                        },
                        error: function(xhr) {
                            showMessage(xhr.responseJSON.error, 'danger');
                        }
                    });
                }
            });

            $(document).on('click', '.update-status', function() {
                let appointmentId = $(this).data('id');
                let newStatus = $(this).data('status');
                $.ajax({
                    url: '../api/admin_api.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        update_appointment_status: true,
                        appointment_id: appointmentId,
                        new_status: newStatus
                    }),
                    success: function(response) {
                        showMessage(response.message, 'success');
                        loadContent();
                    },
                    error: function(xhr) {
                        showMessage(xhr.responseJSON.error, 'danger');
                    }
                });
            });

            $(document).on('click', '.delete-appointment', function() {
                if (confirm('Biztosan törli ezt az időpontot?')) {
                    let appointmentId = $(this).data('id');
                    $.ajax({
                        url: '../api/admin_api.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            delete_appointment: true,
                            appointment_id: appointmentId
                        }),
                        success: function(response) {
                            showMessage(response.message, 'success');
                            loadContent();
                        },
                        error: function(xhr) {
                            showMessage(xhr.responseJSON.error, 'danger');
                        }
                    });
                }
            });

            $('#searchInput').on('input', function() {
                const query = this.value.toLowerCase();
                if (currentTab === 'users') {
                    $('#content-body tbody tr').each(function() {
                        const name = $(this).data('name') || '';
                        const email = $(this).data('email') || '';
                        $(this).css('display', name.includes(query) || email.includes(query) ? '' : 'none');
                    });
                } else if (currentTab === 'clients') {
                    $('#content-body tbody tr').each(function() {
                        const name = $(this).data('name') || '';
                        const user = $(this).data('user') || '';
                        $(this).css('display', name.includes(query) || user.includes(query) ? '' : 'none');
                    });
                } else if (currentTab === 'appointments') {
                    $('#appointmentList .subscription-card').each(function() {
                        const title = $(this).data('title').toLowerCase();
                        const user = $(this).data('user').toLowerCase();
                        const client = $(this).data('client').toLowerCase();
                        const description = $(this).data('description').toLowerCase();
                        $(this).css('display', title.includes(query) || user.includes(query) || client.includes(query) || description.includes(query) ? '' : 'none');
                    });
                } else if (currentTab === 'subscriptions') {
                    $('#subscriptionList .subscription-card').each(function() {
                        const client = $(this).data('client').toLowerCase();
                        const user = $(this).data('user').toLowerCase();
                        const service = $(this).data('service').toLowerCase();
                        $(this).css('display', client.includes(query) || user.includes(query) || service.includes(query) ? '' : 'none');
                    });
                }
            });
        });
    </script>
</body>

</html>