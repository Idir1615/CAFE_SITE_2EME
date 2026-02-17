<?php
// Fonction d'envoi d'email améliorée
function sendReservationEmail($to, $nom, $date, $heure, $personnes, $telephone, $message, $statut, $motif = '') {
    $subject = '';
    $body = '';
    
    if ($statut === 'confirmee') {
        $subject = "✅ Réservation CONFIRMÉE - CM CAFÉ";
        $body = "Bonjour $nom,\n\n";
        $body .= "Votre réservation au CM CAFÉ est CONFIRMÉE !\n\n";
        $body .= "📅 Date : $date\n";
        $body .= "🕐 Heure : $heure\n";
        $body .= "👥 Personnes : $personnes\n";
        $body .= "📱 Téléphone : $telephone\n\n";
        if (!empty($message)) {
            $body .= "Votre message : $message\n\n";
        }
        $body .= "Nous vous attendons avec plaisir !\n\n";
        $body .= "Cordialement,\nL'équipe CM CAFÉ\n25 Rue de la Convention - 75015 PARIS\n01.40.59.86.03";
    } else {
        $subject = "❌ Réservation REFUSÉE - CM CAFÉ";
        $body = "Bonjour $nom,\n\n";
        $body .= "Nous sommes désolés de vous informer que votre réservation au CM CAFÉ a été refusée.\n\n";
        $body .= "📅 Date demandée : $date\n";
        $body .= "🕐 Heure demandée : $heure\n";
        $body .= "👥 Personnes : $personnes\n\n";
        $body .= "⚠️ Motif du refus : $motif\n\n";
        $body .= "N'hésitez pas à nous contacter au 01.40.59.86.03 pour une autre date.\n\n";
        $body .= "Cordialement,\nL'équipe CM CAFÉ";
        $body .= "\n25 Rue de la Convention - 75015 PARIS\n01.";
    }
    
    $headers = "From: noreply@cmcafe.fr\r\n";
    $headers .= "Reply-To: contact@cmcafe.fr\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Pour les tests en local, on simule l'envoi
    if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
        // Log l'email au lieu de l'envoyer
        $log = "\n=== EMAIL SIMULÉ ===\n";
        $log .= "À : $to\n";
        $log .= "Sujet : $subject\n";
        $log .= "Message :\n$body\n";
        $log .= "===================\n";
        file_put_contents('emails_log.txt', $log, FILE_APPEND);
        return true; // Simulation réussie
    } else {
        // En production, envoi réel
        return mail($to, $subject, $body, $headers);
    }
}
?>