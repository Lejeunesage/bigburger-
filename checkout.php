<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_POST['order'])){

   $name = $_POST['name'];
   $name = htmlspecialchars($name);
   $number = $_POST['number'];
   $number = htmlspecialchars($number);
   $email = $_POST['email'];
   $email = htmlspecialchars($email);
   $method = $_POST['method'];
   $method = htmlspecialchars($method);
   $address = 'Appartement n°. '. $_POST['flat'] .' '. $_POST['street'] .' '. $_POST['city'] .' '. $_POST['state'] .' '. $_POST['country'] .' - '. $_POST['pin_code'];
   $address = htmlspecialchars($address);
   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products[] = '';

   $cart_query = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $cart_query->execute([$user_id]);
   if($cart_query->rowCount() > 0){
      while($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)){
         $cart_products[] = $cart_item['name'].' ( '.$cart_item['quantity'].' )';
         $sub_total = ($cart_item['price'] * $cart_item['quantity']);
         $cart_total += $sub_total;
      };
   };

   $total_products = implode(', ', $cart_products);

   $order_query = $conn->prepare("SELECT * FROM `orders` WHERE name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_price = ?");
   $order_query->execute([$name, $number, $email, $method, $address, $total_products, $cart_total]);

   if($cart_total == 0){
      $message[] = 'your cart is empty';
   }elseif($order_query->rowCount() > 0){
      $message[] = 'Commande déjà passée!';
   }else{
      $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES(?,?,?,?,?,?,?,?,?)");
      $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on]);
      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart->execute([$user_id]);
      $message[] = 'Commande passée avec succès !';
   }

}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>checkout</title>

  

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="./css/footer.css">

   
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="display-orders">

   <?php
      $cart_grand_total = 0;
      $select_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart_items->execute([$user_id]);
      if($select_cart_items->rowCount() > 0){
         while($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)){
            $cart_total_price = ($fetch_cart_items['price'] * $fetch_cart_items['quantity']);
            $cart_grand_total += $cart_total_price;
   ?>
   <p> <?= $fetch_cart_items['name']; ?> <span>(<?=$fetch_cart_items['price'].'€ x '. $fetch_cart_items['quantity']; ?>)</span> </p>
   <?php
    }
   }else{
      echo '<p class="empty">Votre panier est vide!</p>';
   }
   ?>
   <div class="grand-total">Grand total : <span><?= $cart_grand_total; ?>€</span></div>
</section>

<section class="checkout-orders">

   <form action="" method="POST">

      <h3>Passer votre commande</h3>

      <div class="flex">
         <div class="inputBox">
            <span>Votre nom complet :</span>
            <input type="text" name="name" placeholder="Entrer votre nom complet" class="box" required>
         </div>
         <div class="inputBox">
            <span>Votre numero :</span>
            <input type="number" name="number" placeholder="Entrer votre numero de téléphone" class="box" required>
         </div>
         <div class="inputBox">
            <span>Votre email :</span>
            <input type="email" name="email" placeholder="Entrer votre email" class="box" required>
         </div>
         <div class="inputBox">
            <span>Mode de paiment :</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">Paiement à la livraison</option>
               <option value="paytm">MTN Mobile Money </option>
               <option value="paypal">Moov flooz</option>
               <option value="credit card">Cart de crédit</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Maison:</span>
            <input type="text" name="flat" placeholder="e.g. Maison Boni" class="box" required>
         </div>
         <div class="inputBox">
            <span>Quartier :</span>
            <input type="text" name="street" placeholder="e.g. Cadjehoun" class="box" required>
         </div>
         <div class="inputBox">
            <span>Ville :</span>
            <input type="text" name="city" placeholder="e.g. Cotonou" class="box" required>
         </div>
         <div class="inputBox">
            <span>Département :</span>
            <input type="text" name="state" placeholder="e.g. Littoral" class="box" required>
         </div>
         <div class="inputBox">
            <span>Pays :</span>
            <input type="text" name="country" placeholder="e.g. Benin" class="box" required>
         </div>
         <div class="inputBox">
            <span>Code pin :</span>
            <input type="number" min="0" name="pin_code" placeholder="e.g. 123456" class="box" required>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($cart_grand_total > 1)?'':'disabled'; ?>" value="Passer la commande">

   </form>

</section>








<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>