<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. E-mailadres van de ontvanger
    $to_email = "cn-plates-updates@scramble.nl";
    $subject = "New Aircraft Plate Update from Website";
    
    // 2. Gegevens ophalen
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $type = strip_tags(trim($_POST["type"]));
    $locations = strip_tags(trim($_POST["locations"]));
    
    $boundary = md5(time());
    
    // Headers voor de e-mail
    $headers = "From: Aircraft Database <" . $to_email . ">\r\n";
    $headers .= "Reply-To: " . $name . " <" . $email . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"\r\n";
    
    // 3. Tekstbericht opbouwen
    $body = "--" . $boundary . "\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    
    $body .= "A user has submitted a new update:\r\n\r\n";
    $body .= "Name: " . $name . "\r\n";
    $body .= "Email: " . $email . "\r\n";
    $body .= "Aircraft Type: " . ($type ? $type : "Not specified") . "\r\n";
    $body .= "c/n locations:\r\n" . ($locations ? $locations : "None provided") . "\r\n\r\n";
    
    // 4. Foto's toevoegen als bijlage
    if (isset($_FILES['photos'])) {
        $total_files = count($_FILES['photos']['name']);
        
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['photos']['error'][$i] == 0) {
                $file_name = $_FILES['photos']['name'][$i];
                $file_tmp = $_FILES['photos']['tmp_name'][$i];
                $file_type = $_FILES['photos']['type'][$i];
                
                $file_content = file_get_contents($file_tmp);
                $encoded_content = chunk_split(base64_encode($file_content));
                
                $body .= "--" . $boundary . "\r\n";
                $body .= "Content-Type: " . $file_type . "; name=\"" . $file_name . "\"\r\n";
                $body .= "Content-Disposition: attachment; filename=\"" . $file_name . "\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $body .= $encoded_content . "\r\n";
            }
        }
    }
    
    $body .= "--" . $boundary . "--";
    
    // 5. Versturen en doorsturen naar de bedankpagina
    if (mail($to_email, $subject, $body, $headers)) {
        header("Location: thanks.html");
        exit();
    } else {
        echo "Sorry, something went wrong while sending your message.";
    }
} else {
    echo "Access denied.";
}
?>
