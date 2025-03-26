<?php
ob_start();
require_once '../includes/header.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szolgáltatások</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            color: #1e293b;
        }

        .service-container {
            padding: 80px 20px;
            max-width: 1300px;
            margin: 0 auto;
        }

        .service-title {
            font-size: 3rem;
            font-weight: 700;
            color: #1e40af;
            text-align: center;
            margin-bottom: 60px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .service-card {
            border: none;
            border-radius: 25px;
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 40px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 2rem;
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }

        .card-text {
            font-size: 1.1rem;
            color: #64748b;
            text-align: center;
            margin-bottom: 25px;
            flex-grow: 1;
        }

        .card-subtitle {
            font-size: 1.5rem;
            color: #e11d48;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
        }

        .btn-primary {
            border-radius: 50px;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 500;
            background: linear-gradient(90deg, #3b82f6, #1e40af);
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #1e40af, #3b82f6);
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 999;
        }

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 900px;
            background: #ffffff;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            border-radius: 30px;
            z-index: 1000;
            padding: 40px;
            animation: slideIn 0.4s ease-out;
        }

        .modal.show {
            display: block;
        }

        .modal-overlay.show {
            display: block;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -60%);
            }

            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        .modal-header {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .modal-title {
            font-size: 2.2rem;
            color: #1e40af;
            font-weight: 600;
        }

        .modal-body {
            font-size: 1.2rem;
            color: #64748b;
        }

        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            margin-top: 30px;
            text-align: right;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .input_label {
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .input_field {
            width: 100%;
            height: 50px;
            padding: 0 20px;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input_field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 8px rgba(59, 130, 246, 0.2);
            background: #ffffff;
        }

        .payment-options {
            display: none;
            padding: 20px;
            background: #f8fafc;
            border-radius: 15px;
            margin-top: 20px;
        }

        .purchase--btn {
            height: 55px;
            background: linear-gradient(90deg, #3b82f6, #1e40af);
            border-radius: 15px;
            border: none;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            transition: all 0.3s ease;
            width: 100%;
        }

        .purchase--btn:hover {
            background: linear-gradient(90deg, #1e40af, #3b82f6);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        }

        .alert {
            margin-top: 20px;
            border-radius: 15px;
        }

        @media (max-width: 768px) {
            .modal {
                max-width: 95%;
                padding: 20px;
            }

            .service-title {
                font-size: 2rem;
            }

            .card-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="service-container">
        <h2 class="service-title">Szolgáltatási csomagok</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center" id="servicesList"></div>
    </div>

    <div class="modal-overlay" id="modalOverlay"></div>

    <div class="modal" id="serviceModal">
        <div class="modal-header">
            <h5 class="modal-title" id="modalTitle"></h5>
            <button class="close-modal" style="background: none; border: none; font-size: 24px; color: #64748b; cursor: pointer;">×</button>
        </div>
        <div class="modal-body">
            <p id="modalDescription"></p>
            <h6 id="modalPrice" class="card-subtitle"></h6>
        </div>
        <div class="modal-footer">
            <button id="nextBtn" class="btn btn-primary">Fizetés Stripe-on keresztül</button>
        </div>
    </div>

    <div id="messageContainer" class="service-container"></div>

    <script>
        $(document).ready(function() {
            loadServices();

            function loadServices() {
                $.getJSON('../api/services.php', function(services) {
                    let html = '';
                    services.forEach(service => {
                        html += `
                            <div class="col">
                                <div class="service-card h-100">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">${service.service_name}</h5>
                                        <p class="card-text">${service.service_description.replace(/\n/g, '<br>')}</p>
                                        <h6 class="card-subtitle">${Number(service.service_price).toLocaleString('hu-HU')} Ft/hó</h6>
                                        <button class="btn btn-primary mt-3 w-100 open-service-modal"
                                            data-id="${service.id}"
                                            data-name="${service.service_name}"
                                            data-description="${service.service_description}"
                                            data-price="${service.service_price}">
                                            Feliratkozás
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    $('#servicesList').html(html);
                }).fail(function(xhr, status, error) {
                    showMessage('Hiba történt a szolgáltatások betöltésekor: ' + error, 'danger');
                });
            }

            $(document).on('click', '.open-service-modal', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const description = $(this).data('description');
                const price = $(this).data('price');
                $('#modalTitle').text(name);
                $('#modalDescription').html(description.replace(/\n/g, '<br>'));
                $('#modalPrice').text(`${Number(price).toLocaleString('hu-HU')} Ft/hó`);
                $('#serviceModal').addClass('show');
                $('#modalOverlay').addClass('show');
                $('#serviceModal').data('service_id', id);
                $('#serviceModal').data('price', price);
            });

            $('#nextBtn').on('click', function() {
                submitPayment({
                    service_id: $('#serviceModal').data('service_id'),
                    price: $('#serviceModal').data('price')
                });
            });

            $(document).on('click', '.close-modal', function() {
                $(this).closest('.modal').removeClass('show');
                $('#modalOverlay').removeClass('show');
            });

            $(document).on('click', '#modalOverlay', function() {
                $('.modal').removeClass('show');
                $('#modalOverlay').removeClass('show');
            });

            function submitPayment(data) {
                console.log('Küldött adat:', data); // Debug
                $.ajax({
                    url: '../api/services.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    success: function(response) {
                        console.log('Szerver válasza:', response); // Debug
                        if (response.error) {
                            showMessage(response.error, 'danger');
                        } else {
                            showMessage(response.message, 'success');
                            setTimeout(() => window.location.href = response.payment_url, 1000);
                        }
                    },
                    error: function(xhr) {
                        console.log('AJAX hiba:', xhr.responseText); // Debug
                        showMessage('Hiba történt: ' + xhr.status + ' - ' + xhr.statusText, 'danger');
                    }
                });
            }

            $('#nextBtn').on('click', function() {
                submitPayment({
                    service_id: $('#serviceModal').data('service_id'),
                    price: $('#serviceModal').data('price')
                });
            });

            function showMessage(message, type) {
                $('#messageContainer').html(`<div class="alert alert-${type}">${message}</div>`);
                setTimeout(() => $('#messageContainer').empty(), 3000);
            }
        });
    </script>
</body>

</html>

<?php require_once '../includes/footer.php'; ?>