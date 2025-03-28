<?php  
require_once '../includes/header.php';

?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Könyvelő Iroda - Dokumentumok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f0f4f8, #334155);
            font-family: 'Roboto', sans-serif;
            min-height: 100vh;
            color: #e2e8f0;
            padding: 2rem;
        }

        .content-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            font-weight: 700;
            color: #ffffff;
            text-align: center;
            margin-bottom: 2.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            animation: fadeIn 1s ease-out;
        }

        .document-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .document-card {
            background: #2d3748;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeInUp 0.5s ease-out;
        }

        .document-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        }

        .document-card h5 {
            font-weight: 400;
            color: #ffffff;
            margin-bottom: 0.75rem;
            font-size: 1.25rem;
        }

        .document-card p {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.6rem 1.2rem;
            background: #10b981;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 400;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .download-btn:hover {
            background: #059669;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .download-btn i {
            margin-right: 0.5rem;
        }

        .no-documents {
            text-align: center;
            color: #94a3b8;
            font-size: 1.2rem;
            padding: 3rem;
            background: #2d3748;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
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

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .document-grid {
                grid-template-columns: 1fr;
            }

            .document-card {
                padding: 1.2rem;
            }

            .download-btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <h1>Letölthető Dokumentumok</h1>
        <div id="document-container"></div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetch("../api/documents.php")
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP hiba: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const container = document.getElementById("document-container");

                    if (data.error) {
                        container.innerHTML = `<div class="no-documents">${data.error}</div>`;
                        return;
                    }

                    if (data.length > 0) {
                        let grid = '<div class="document-grid">';
                        data.forEach(doc => {
                            grid += `
                                <div class="document-card">
                                    <h5>${escapeHtml(doc.title)}</h5>
                                    <p>${escapeHtml(doc.description)}</p>
                                    <a href="download.php?file=${encodeURIComponent(doc.DataFile)}" class="download-btn" download>
                                        <i class="fas fa-download"></i> Letöltés
                                    </a>
                                </div>
                            `;
                        });
                        grid += '</div>';
                        container.innerHTML = grid;
                    } else {
                        container.innerHTML = '<div class="no-documents">Jelenleg nincsenek letölthető dokumentumok.</div>';
                    }
                })
                .catch(error => {
                    console.error("Hiba:", error);
                    document.getElementById("document-container").innerHTML =
                        `<div class="no-documents">Hiba történt az adatok betöltésekor: ${error.message}</div>`;
                });
        });

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }
    </script>
</body>

</html>