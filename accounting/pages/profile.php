<?php
ob_start();
session_start();
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeInUp 0.5s ease-out;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
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

        .profile-avatar {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 4px solid #1976d2;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .profile-avatar:hover {
            transform: scale(1.05);
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

        .btn-danger {
            background: #d32f2f;
            border: none;
        }

        .btn-secondary {
            background: #607d8b;
            border: none;
        }

        .btn-sm {
            padding: 0.4rem 1rem;
            font-size: 0.875rem;
        }

        .subscription-card,
        .payment-card {
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .subscription-card:hover,
        .payment-card:hover {
            transform: translateY(-5px);
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

            .profile-avatar {
                width: 120px;
                height: 120px;
            }

            .btn {
                padding: 0.5rem 1.2rem;
            }
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <div id="message-container"></div>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center" id="profile-summary"></div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Profil szerkesztése</h5>
                    </div>
                    <div class="card-body">
                        <form id="profile-form" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Teljes név</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email cím</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Felhasználónév</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Profil mentése</button>
                        </form>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Aktív előfizetéseim</h5>
                    </div>
                    <div class="card-body" id="subscriptions-container"></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Jelszó módosítása</h5>
                    </div>
                    <div class="card-body">
                        <form id="password-form" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Jelenlegi jelszó</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Új jelszó</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>
                                <div class="form-text">A jelszónak legalább 8 karaktert, egy számot, egy kis- és egy nagybetűt kell tartalmaznia.</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Új jelszó megerősítése</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Jelszó módosítása</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const stripe = Stripe('pk_test_51R5NbyHUv7jEVnHmkRdayeXrNQhu42x39hb1LsgXN6Cgmm9tKNIP7oi15uoBNmKPvkPAoLwqINFTeMKSj6JPrwsX00e2f2cqJN');

        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            container.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }

        document.addEventListener("DOMContentLoaded", function() {
            fetch("../api/profile.php", {
                    method: "GET",
                    headers: {
                        "Accept": "application/json"
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP hiba: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    console.log("API Response:", data);
                    if (data.error) {
                        showMessage(data.error, "danger");
                        return;
                    }

                    const profileSummary = document.getElementById("profile-summary");
                    profileSummary.innerHTML = `
                <h4>${data.user.name}</h4>
                <p class="text-muted"><i class="fas fa-envelope"></i> ${data.user.email}</p>
                <p class="text-muted"><i class="fas fa-user"></i> ${data.user.username}</p>
            `;

                    document.getElementById("name").value = data.user.name;
                    document.getElementById("email").value = data.user.email;
                    document.getElementById("username").value = data.user.username;

                    const subscriptionsContainer = document.getElementById("subscriptions-container");
                    if (!data.subscriptions || data.subscriptions.length === 0) {
                        subscriptionsContainer.innerHTML = `
                    <p class="text-muted">Nincs előfizetésed. <a href="services.php" class="btn btn-primary btn-sm" id="new-subscription-btn">Új előfizetés hozzáadása</a></p>
                `;
                    } else {
                        let html = '<div class="row">';
                        data.subscriptions.forEach(sub => {
                            html += `
                        <div class="col-md-6 mb-3">
                            <div class="card subscription-card">
                                <div class="card-body">
                                    <h6>${sub.service_name}</h6>
                                    <p>Összeg: ${sub.service_price} Ft/hó</p>
                                    <p>Kezdete: ${new Date(sub.start_date).toLocaleDateString('hu-HU')}</p>
                                    <form class="mt-2 cancel-subscription-form" data-subscription-id="${sub.id}">
                                        <button type="submit" class="btn btn-danger btn-sm">Lemondás</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    `;
                        });
                        html += '</div><p><a href="services.php" class="btn btn-primary" id="new-subscription-btn">Új előfizetés hozzáadása</a></p>';
                        subscriptionsContainer.innerHTML = html;
                    }

                    const newSubscriptionBtn = document.getElementById("new-subscription-btn");
                    if (newSubscriptionBtn) {
                        newSubscriptionBtn.addEventListener("click", function(e) {
                            e.preventDefault();
                            fetch("../api/profile.php", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json"
                                    },
                                    body: JSON.stringify({
                                        create_subscription: true,
                                        service_id: 1
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.session_id) {
                                        stripe.redirectToCheckout({
                                            sessionId: data.session_id
                                        });
                                    } else {
                                        showMessage("Hiba az előfizetés létrehozásakor", "danger");
                                    }
                                })
                                .catch(error => showMessage("Hiba: " + error.message, "danger"));
                        });
                    }
                })
                .catch(error => {
                    console.error("Fetch error:", error);
                    showMessage("Hiba történt az adatok betöltésekor: " + error.message, "danger");
                });
        });
        document.getElementById("profile-form").addEventListener("submit", function(e) {
            e.preventDefault();
            if (!this.checkValidity()) return;

            const data = {
                name: document.getElementById("name").value,
                email: document.getElementById("email").value,
                username: document.getElementById("username").value
            };

            fetch("../api/profile.php", {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP hiba: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    showMessage(data.message || data.error, data.message ? "success" : "danger");
                    if (data.message) location.reload();
                })
                .catch(error => showMessage("Hiba: " + error.message, "danger"));
        });

        $(document).on("submit", ".cancel-subscription-form", function(e) {
            e.preventDefault();
            if (!confirm("Biztosan lemondod ezt az előfizetést?")) return;

            const subscriptionId = $(this).data("subscription-id");
            fetch("../api/profile.php", {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        subscription_id: subscriptionId
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errData => {
                            throw new Error(`HTTP hiba: ${response.status} - ${errData.error || 'Ismeretlen hiba'}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    showMessage(data.message || data.error, data.message ? "success" : "danger");
                    if (data.message) location.reload();
                })
                .catch(error => {
                    console.error("DELETE kérés hibája:", error);
                    showMessage("Hiba: " + error.message, "danger");
                });
        });
        document.getElementById("password-form").addEventListener("submit", function(e) {
            e.preventDefault();
            if (!this.checkValidity()) return;

            const data = {
                change_password: true,
                current_password: document.getElementById("current_password").value,
                new_password: document.getElementById("new_password").value,
                confirm_password: document.getElementById("confirm_password").value
            };

            fetch("../api/profile.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP hiba: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    showMessage(data.message || data.error, data.message ? "success" : "danger");
                    if (data.message) location.reload();
                })
                .catch(error => showMessage("Hiba: " + error.message, "danger"));
        });

        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    if (form.querySelector('#new_password')) {
                        var password = form.querySelector('#new_password');
                        var confirm = form.querySelector('#confirm_password');
                        if (password.value !== confirm.value) {
                            confirm.setCustomValidity('A jelszavak nem egyeznek!');
                            event.preventDefault();
                            event.stopPropagation();
                        } else {
                            confirm.setCustomValidity('');
                        }
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>

    <?php require_once '../includes/footer.php'; ?>
</body>

</html>