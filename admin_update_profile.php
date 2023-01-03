<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
};

if(isset($_POST['update_profile'])){

   $name = $_POST['name'];
   $name = htmlspecialchars($name);
   $email = $_POST['email'];
   $email = htmlspecialchars($email);

   $update_profile = $conn->prepare("UPDATE `users` SET name = ?, email = ? WHERE id = ?");
   $update_profile->execute([$name, $email, $admin_id]);

   $image = $_FILES['image']['name'];
   $image = htmlspecialchars($image);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;
   $old_image = $_POST['old_image'];

   if(!empty($image)){
      if($image_size > 5000000){
         $message[] = 'La taille de l\'image est trop grande !';
      }else{
         $update_image = $conn->prepare("UPDATE `users` SET image = ? WHERE id = ?");
         $update_image->execute([$image, $admin_id]);
         if($update_image){
            move_uploaded_file($image_tmp_name, $image_folder);
            unlink('uploaded_img/'.$old_image);
            $message[] = 'Image mise à jour avec succès !';
         };
      };
   };

   $old_pass = $_POST['old_pass'];
   $update_pass = md5($_POST['update_pass']);
   $update_pass = htmlspecialchars($update_pass);
   $new_pass = md5($_POST['new_pass']);
   $new_pass = htmlspecialchars($new_pass);
   $confirm_pass = md5($_POST['confirm_pass']);
   $confirm_pass = htmlspecialchars($confirm_pass);

   if(!empty($update_pass) AND !empty($new_pass) AND !empty($confirm_pass)){
      if($update_pass != $old_pass){
         $message[] = 'L\'ancien mot de passe ne correspond pas !';
      }elseif($new_pass != $confirm_pass){
         $message[] = 'Le mot de passe ne correspond pas !';
      }else{
         $update_pass_query = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
         $update_pass_query->execute([$confirm_pass, $admin_id]);
         $message[] = 'Mot de passe mis à jour avec succès!';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Mettre à jour le profil administrateur</title>

  

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/components.css">
   <link rel="stylesheet" href="./css/footer.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="update-profile">

   <h1 class="title">Mettre à jour le profil</h1>

   <form action="" method="POST" enctype="multipart/form-data">
      <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
      <div class="flex">
         <div class="inputBox">
            <span>Nom d'utilisateur :</span>
            <input type="text" name="name" value="<?= $fetch_profile['name']; ?>" placeholder="Mettre à jour le nom d'utilisateur" required class="box">
            <span>Email :</span>
            <input type="email" name="email" value="<?= $fetch_profile['email']; ?>" placeholder="mettre à jour l'email" required class="box">
            <span>Mettre à jour la photo :</span>
            <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box">
            <input type="hidden" name="old_image" value="<?= $fetch_profile['image']; ?>">
         </div>
         <div class="inputBox">
            <input type="hidden" name="old_pass" value="<?= $fetch_profile['password']; ?>">
            <span>Ancien mot de passe :</span>
            <input type="password" name="update_pass" placeholder="Entrer le mot de passe précédent" class="box">
            <span>Nouveau mot de passe :</span>
            <input type="password" name="new_pass" placeholder="Entrez un nouveau mot de passe" class="box">
            <span>Confirmez le mot de passe :</span>
            <input type="password" name="confirm_pass" placeholder="Confirmer le nouveau mot de passe" class="box">
         </div>
      </div>
      <div class="flex-btn">
         <input type="submit" class="btn" value="Mettre à jour le profil" name="update_profile">
         <a href="admin_page.php" class="option-btn">Retour</a>
      </div>
   </form>

</section>













<script src="js/script.js"></script>

</body>
</html>