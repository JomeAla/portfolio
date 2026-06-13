<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");
if ($conn->connect_error) { die("DB Error"); }

$products = [
    1=>['n'=>'Email Sequence Templates Pack','f'=>'email-templates-pack.zip'],
    2=>['n'=>'Email Marketing Premium Bundle','f'=>'premium-bundle.zip'],
    3=>['n'=>'Done-For-You Email Automation','f'=>'dfy-automation.zip'],
    4=>['n'=>'WhatsApp Marketing Bundle','f'=>'whatsapp-bundle.zip'],
    5=>['n'=>'Course Creator Kit','f'=>'course-creator-kit.zip'],
    6=>['n'=>'Local Business Digital Kit','f'=>'local-business-kit.zip'],
    7=>['n'=>'SaaS Starter Kit','f'=>'saas-starter-kit.zip'],
    8=>['n'=>'Freelancer Toolkit','f'=>'freelancer-toolkit.zip'],
    9=>['n'=>'Instagram Growth System','f'=>'instagram-growth.zip'],
    10=>['n'=>'Nigerian Business Digital Kit','f'=>'nigeria-business-kit.zip'],
    11=>['n'=>'Church Website Kit','f'=>'church-website-kit.zip'],
    12=>['n'=>'Restaurant POS Kit','f'=>'restaurant-pos-kit.zip'],
    13=>['n'=>'School Management System','f'=>'school-mgmt-system.zip'],
    14=>['n'=>'Real Estate Property Kit','f'=>'real-estate-kit.zip'],
    15=>['n'=>'E-commerce Starter Kit','f'=>'ecommerce-starter-kit.zip'],
    16=>['n'=>'WordPress Starter Kit','f'=>'wordpress-starter-kit.zip'],
    17=>['n'=>'Website Audit Kit','f'=>'website-audit-kit.zip'],
];

echo "<h2>Setting up all sequences...</h2>";
$count = 0;

foreach ($products as $pid=>$p) {
    $seq_name = "Post-Purchase - ".$p['n'];
    
    $res = $conn->query("SELECT id FROM email_sequences WHERE name='$seq_name'");
    if ($res && $r=$res->fetch_assoc()) {
        $seq_id = $r['id'];
    } else {
        $conn->query("INSERT INTO email_sequences (name,description,is_active) VALUES ('$seq_name','Post-purchase for ".$p['n']."',1)");
        $seq_id = $conn->insert_id;
    }
    
    $conn->query("DELETE FROM sequence_steps WHERE sequence_id=$seq_id");
    
    $steps = [
        ["Thank you for your purchase! Here is your download link:", "Your ".$p['n']." is ready! Download: https://joala.com.ng/downloads/".$p['f'], 0, 1],
        ["Quick start guide...", "Here are steps to get started with ".$p['n'], 48, 2],
        ["Need help? Reply here", "Questions? Just reply!", 120, 3]
    ];
    
    foreach ($steps as $s) {
        $sub = $conn->real_escape_string($s[0]);
        $con = $conn->real_escape_string($s[1]);
        $conn->query("INSERT INTO sequence_steps (seq_id,sbj,cont,delay,so) VALUES ($seq_id,'$sub','$con',".$s[2].",".$s[3].")");
    }
    
    $conn->query("UPDATE funnels SET welcome_sequence_id=$seq_id WHERE product_id=$pid");
    echo $p['n']." = Seq $seq_id<br>";
    $count++;
}

echo "<h2>Done! ($count products)</h2>";

$conn->close();