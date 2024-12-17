<?php
// Configuration de la base de données
$host = 'localhost';
$dbname = 'database';  // Remplace 'database' par le nom de ta base de données
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Nombre de produits par page
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Récupération des produits
$stmt = $pdo->prepare("SELECT * FROM products LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer le nombre total de produits
$totalStmt = $pdo->query("SELECT COUNT(*) FROM products");
$totalProducts = $totalStmt->fetchColumn();

// Si la requête est en AJAX, renvoyer les produits en JSON
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    echo json_encode(['products' => $products, 'total' => $totalProducts]);
    exit;
}

$totalPages = ceil($totalProducts / $limit);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Défilement Infini des Produits</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .loading {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4 text-center">Liste des Produits</h1>
    <div class="row" id="product-list">
        <!-- Les produits seront chargés ici dynamiquement -->
    </div>

    <div class="loading" id="loading-message" style="display: none;">
        <p>Chargement...</p>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let page = 1;  // La première page
    let loading = false;  // Pour éviter de charger plusieurs fois en même temps

    function loadProducts() {
        if (loading) return;  // Si une requête est déjà en cours, ne pas charger à nouveau
        loading = true;
        $('#loading-message').show();  // Affiche le message de chargement

        $.ajax({
            url: 'index.php',  // L'URL de la page PHP qui renvoie les produits
            data: { page: page, ajax: 1 },  // La page actuelle et le paramètre AJAX
            method: 'GET',
            success: function (data) {
                data = JSON.parse(data);  // Convertir la réponse JSON en objet JavaScript

                // Ajouter les produits au DOM
                let productsHTML = '';
                data.products.forEach(function(product) {
                    productsHTML += `
                        <div class="col-md-3 mb-4">
                            <div class="card">
                                <img src="${product.image_url}" class="card-img-top" alt="Image Produit">
                                <div class="card-body">
                                    <h5 class="card-title">${product.name}</h5>
                                    <p class="card-text">${product.description}</p>
                                    <p class="fw-bold">${parseFloat(product.price).toFixed(2)} €</p>
                                </div>
                            </div>
                        </div>
                    `;
                });
                $('#product-list').append(productsHTML);  // Ajouter les produits à la liste

                page++;  // Incrémenter la page pour la prochaine requête
                loading = false;  // Permettre une nouvelle requête
                $('#loading-message').hide();  // Masquer le message de chargement
            }
        });
    }

    // Charger les produits lorsque la page se charge
    $(document).ready(function() {
        loadProducts();

        // Détecter quand l'utilisateur atteint le bas de la page
        $(window).scroll(function() {
            if ($(window).scrollTop() + $(window).height() == $(document).height()) {
                loadProducts();
            }
        });
    });
</script>
</body>
</html>
