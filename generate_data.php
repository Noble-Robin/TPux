<?php
require 'vendor/autoload.php';
$host = 'localhost';
$dbname = 'database';
$user = 'root';
$password = '';

$faker = Faker\Factory::create();

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);

    for ($i = 0; $i < 1000; $i++) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $faker->words(3, true),
            $faker->sentence(),
            $faker->randomFloat(2, 5, 500),
            $faker->imageUrl(200, 200, 'technics')
        ]);
    }
    echo "Données insérées avec succès !";
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
