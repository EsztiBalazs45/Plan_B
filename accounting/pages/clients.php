<?php
require_once '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ügyfelek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f0f4f8, #334155);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            color: #263238;
        }

        .content-wrapper {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            background: #ffffff;
            animation: fadeInUp 0.5s ease-out;
        }

        .card-header {
            background: linear-gradient(135deg, #1976d2, #42a5f5);
            color: #ffffff;
            border-radius: 20px 20px 0 0;
            padding: 1.5rem;
            font-weight: 600;
        }

        .card-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 500;
            color: #263238;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #b0bec5;
            padding: 0.75rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: #1976d2;
            box-shadow: 0 0 5px rgba(25, 118, 210, 0.3);
        }

        .btn {
            border-radius: 50px;
            padding: 0.6rem 1.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: #1976d2;
            border: none;
        }

        .btn-secondary {
            background: #607d8b;
            border: none;
        }

        .btn-danger {
            background: #d32f2f;
            border: none;
        }

        .table {
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .table thead {
            background: #e3f2fd;
            color: #0d47a1;
        }

        .table th,
        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .table tbody tr {
            transition: background 0.3s ease;
        }

        .table tbody tr:hover {
            background: #f5f7fa;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }

        .text-muted {
            color: #78909c;
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

        @media (max-width: 768px) {
            .content-wrapper {
                padding: 1rem;
            }

            .card-body {
                padding: 1.5rem;
            }

            .btn {
                padding: 0.5rem 1.2rem;
            }
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <div class="card" id="client-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Ügyfelek kezelése</h5>
                <button class="btn btn-primary" id="new-client-btn"><i class="fas fa-plus"></i> Új ügyfél</button>
            </div>
            <div class="card-body" id="client-content">
                <div class="table-responsive" id="client-list"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientModalLabel">Új ügyfél hozzáadása</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="clientForm" class="needs-validation" novalidate>
                        <input type="hidden" id="client_id" name="client_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Cégnév</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tax_number" class="form-label">Adószám</label>
                                <input type="text" class="form-control" id="tax_number" name="tax_number" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="reg_number" class="form-label">Cégjegyzékszám</label>
                                <input type="text" class="form-control" id="reg_number" name="registration_number" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="headquarters" class="form-label">Székhely</label>
                                <input type="text" class="form-control" id="headquarters" name="headquarters" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact_person" class="form-label">Kapcsolattartó neve</label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="contact_number" class="form-label">Kapcsolattartó telefonszáma</label>
                                <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezárás</button>
                            <button type="submit" class="btn btn-primary" id="save-client-btn">Mentés</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Ügyfelek betöltése
            function loadClients() {
                $.getJSON('../api/clients.php', function(clients) {
                    let table = `
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cégnév</th>
                                    <th>Adószám</th>
                                    <th>Kapcsolattartó</th>
                                    <th>Telefonszám</th>
                                    <th>Műveletek</th>
                                </tr>
                            </thead>
                            <tbody>`;
                    if (clients.length > 0) {
                        clients.forEach(client => {
                            table += `
                                <tr>
                                    <td>${escapeHtml(client.CompanyName)}</td>
                                    <td>${escapeHtml(client.tax_number)}</td>
                                    <td>${escapeHtml(client.contact_person)}</td>
                                    <td>${escapeHtml(client.contact_number)}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-primary edit-client" data-id="${client.id}" title="Szerkesztés">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-client" data-id="${client.id}" title="Törlés">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>`;
                        });
                    } else {
                        table += '<tr><td colspan="5" class="text-center text-muted">Még nincsenek ügyfelek.</td></tr>';
                    }
                    table += '</tbody></table>';
                    $('#client-list').html(table);
                }).fail(function(xhr) {
                    alert('Hiba az ügyfelek betöltésekor: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Ismeretlen hiba'));
                });
            }

            // Oldal betöltésekor ügyfelek lekérése
            loadClients();

            // Új ügyfél gomb
            $('#new-client-btn').click(function() {
                $('#clientForm')[0].reset();
                $('#client_id').val('');
                $('#clientModalLabel').text('Új ügyfél hozzáadása');
                $('#save-client-btn').text('Ügyfél hozzáadása');
                $('#clientModal').modal('show');
            });

            // Ügyfél szerkesztése
            $(document).on('click', '.edit-client', function() {
                let clientId = $(this).data('id');
                $.getJSON(`../api/clients.php?action=edit&id=${clientId}`, function(client) {
                    $('#client_id').val(client.id);
                    $('#company_name').val(client.CompanyName);
                    $('#tax_number').val(client.tax_number);
                    $('#reg_number').val(client.registration_number);
                    $('#headquarters').val(client.headquarters);
                    $('#contact_person').val(client.contact_person);
                    $('#contact_number').val(client.contact_number);
                    $('#clientModalLabel').text('Ügyfél szerkesztése');
                    $('#save-client-btn').text('Módosítások mentése');
                    $('#clientModal').modal('show');
                }).fail(function(xhr) {
                    alert('Hiba az ügyfél adatainak lekérésekor: ' + xhr.responseJSON.error);
                });
            });

            $('#clientForm').submit(function(e) {
                e.preventDefault();
                if (!this.checkValidity()) {
                    e.stopPropagation();
                    $(this).addClass('was-validated');
                    return;
                }

                let data = {
                    client_id: $('#client_id').val(),
                    company_name: $('#company_name').val(),
                    tax_number: $('#tax_number').val(),
                    registration_number: $('#reg_number').val(),
                    headquarters: $('#headquarters').val(),
                    contact_person: $('#contact_person').val(),
                    contact_number: $('#contact_number').val()
                };

                let method = data.client_id ? 'PUT' : 'POST';
                $.ajax({
                    url: '../api/clients.php',
                    method: method,
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    success: function(response) {
                        alert(response.message);
                        $('#clientModal').modal('hide');
                        loadClients();
                    },
                    error: function(xhr) {
                        alert('Hiba: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Ismeretlen hiba'));
                    }
                });
            });

            $(document).on('click', '.delete-client', function() {
                if (confirm('Biztosan törölni szeretné ezt az ügyfelet?')) {
                    let clientId = $(this).data('id');
                    $.ajax({
                        url: '../api/clients.php',
                        method: 'DELETE',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            client_id: clientId
                        }),
                        success: function(response) {
                            alert(response.message);
                            loadClients();
                        },
                        error: function(xhr) {
                            alert('Hiba: ' + xhr.responseJSON.error);
                        }
                    });
                }
            });
        });

        function escapeHtml(text) {
            const map = {
                '&': '&',
                '<': '<',
                '>': '>',
                '"': '"',
                "'": "'"
            };
            return text.replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }
    </script>
</body>

</html>