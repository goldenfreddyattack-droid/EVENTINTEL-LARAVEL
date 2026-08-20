<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');

$event_id = intval($_GET['id'] ?? 0);

// Validate event ownership
$ev = db()->prepare("SELECT * FROM events WHERE event_id=? AND user_id=?");
$ev->execute([$event_id, $_SESSION['user_id']]);
$event = $ev->fetch();

if (!$event) {
    header('Location: yourevents.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bg = null;

    // Background upload
    if (isset($_FILES['background']) && $_FILES['background']['error'] === UPLOAD_ERR_OK) {
        $name = 'invitations/bg_' . uniqid() . '.' . pathinfo($_FILES['background']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['background']['tmp_name'], __DIR__ . '/../../uploads/' . $name);
        $bg = 'uploads/' . $name;
    }

    // Check if invitation exists
    $exists = db()->prepare("SELECT invitation_id FROM invitations WHERE event_id=?");
    $exists->execute([$event_id]);

    if ($exists->fetch()) {
        $sql = "UPDATE invitations
                SET title=?, message=?, theme_color=?, font_style=?, button_text=?, template=?"
                . ($bg ? ", background_image=?" : "") . " 
                WHERE event_id=?";
        $params = [
            $_POST['title'],
            $_POST['message'],
            $_POST['theme_color'],
            $_POST['font_style'],
            $_POST['button_text'],
            $_POST['template']
        ];
        if ($bg) $params[] = $bg;
        $params[] = $event_id;
        db()->prepare($sql)->execute($params);
    } else {
        db()->prepare("INSERT INTO invitations(
                                    event_id,
                                    title,
                                    message,
                                    theme_color,
                                    font_style,
                                    button_text,
                                    background_image,
                                    template
                                    )
                                    VALUES(?,?,?,?,?,?,?,?)")
           ->execute([
               $event_id,
                $_POST['title'],
                $_POST['message'],
                $_POST['theme_color'],
                $_POST['font_style'],
                $_POST['button_text'],
                $bg,
                $_POST['template']
           ]);
    }
}

// Load invitation
$inv = db()->prepare("SELECT * FROM invitations WHERE event_id=?");
$inv->execute([$event_id]);
$inv = $inv->fetch() ?: [
    'title' => "You're Invited",
    'message' => 'Please RSVP',
    'theme_color' => '#f3c547',
    'font_style' => 'Segoe UI',
    'button_text' => 'Confirm RSVP',
    'background_image' => null,
    'template' => 'Classic'
];

$link = "/EVENTINTELmayAPI/userui/html/rsvp.php?event=" . $event_id;
if (empty($inv['template']) || $inv['template'] == 'Classic') {

    switch (strtolower($event['event_type'])) {

        case 'wedding':
            $inv['template'] = 'Wedding';
            break;

        case 'birthday':
            $inv['template'] = 'Birthday';
            break;

        case 'corporate':
            $inv['template'] = 'Corporate';
            break;

        case 'debut':
            $inv['template'] = 'Elegant';
            break;

        default:
            $inv['template'] = 'Classic';
    }

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Invitation</title>
    <style>
        body {
            background: #ffffff;
            color: #222;
            font-family: Segoe UI;
            padding: 30px;
        }

        .wrap {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .card {
            background: #fff;
            border: 1px solid rgba(243, 197, 71, 0.4);
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border-radius: 12px;
            background: #f9f9f9;
            color: #222;
            border: 1px solid #ccc;
        }

        .btn {
            padding: 12px 18px;
            border-radius: 12px;
            border: 0;
            background: linear-gradient(135deg, #fff1a8, #f3c547, #c98f08);
            font-weight: 700;
            color: #222;
            cursor: pointer;
        }

        .preview {
            min-height:500px;
            display:flex;
            flex-direction:column;
            justify-content:center;
            align-items:center;
            text-align:center;
            transition:.4s;
            position:relative;
            overflow:hidden;
        }
        .preview h1{
            font-size:46px;
            margin-bottom:25px;
        }

        .preview p{
            font-size:22px;
            line-height:1.8;
        }
        .preview::before{
            position:absolute;
            left:20px;
            top:15px;
            font-size:40px;
            color:gold;
        }

        .preview::after{
            position:absolute;
            right:20px;
            bottom:15px;
            font-size:40px;
            color:gold;
        }
        /* ===== Invitation Templates ===== */

        .preview.wedding{
            border:none;
        }

        .preview.birthday{
            border:none;
        }

        .preview.corporate{
            border:none;
        }

        .preview.elegant{
            border:none;
        }

        .preview.classic{
            border:none;
        }

    </style>
</head>
<body>

<a style="color:#f3c547" href="yourevents.php">← Back</a>
<h1>Editable RSVP Invitation</h1>

<div class="wrap">
    <!-- Form -->
    <form class="card" method="POST" enctype="multipart/form-data">
        <label>Invitation Template</label>

        <select name="template" id="template">

        <option value="Classic"
        <?= $inv['template']=='Classic'?'selected':'' ?>>
        Classic
        </option>

        <option value="Wedding"
        <?= $inv['template']=='Wedding'?'selected':'' ?>>
        Wedding
        </option>

        <option value="Birthday"
        <?= $inv['template']=='Birthday'?'selected':'' ?>>
        Birthday
        </option>

        <option value="Corporate"
        <?= $inv['template']=='Corporate'?'selected':'' ?>>
        Corporate
        </option>

        <option value="Elegant"
        <?= $inv['template']=='Elegant'?'selected':'' ?>>
        Elegant
        </option>

        </select>
        <label>Invitation Title</label>
        <input name="title" value="<?=esc($inv['title'])?>">

        <label>Message</label>
        <textarea name="message"><?=esc($inv['message'])?></textarea>

        <label>Theme Color</label>
        <input type="color" name="theme_color" value="<?=esc($inv['theme_color'])?>">

        <label>Font</label>
        <select name="font_style">
            <option>Segoe UI</option>
            <option>Georgia</option>
            <option>Arial</option>
        </select>
        

        <label>RSVP Button Text</label>
        <input name="button_text" value="<?=esc($inv['button_text'])?>">

        <label>Background Image</label>
        <input type="file" name="background" accept="image/*">

        <button class="btn">Save Template</button>
    </form>

    <!-- Preview -->
    <div class="card">
        <h2>Preview / Share</h2>
        <?php
$templateImage = '';

switch(strtolower($inv['template'])){

    case 'wedding':
        $templateImage = 'images/wedding.jpg';
        break;

    case 'birthday':
        $templateImage = 'images/bday.jpg';
        break;

    case 'corporate':
        $templateImage = 'images/corporate.jpg';
        break;

    case 'elegant':
        $templateImage = 'images/elegant.jpg';
        break;

    default:
        $templateImage = 'images/classic.jpg';
}
?>

        <div
            class="preview <?= strtolower($inv['template']) ?>"
            id="preview"
            style="
                font-family:<?= esc($inv['font_style']) ?>;
                background-image:url('<?= !empty($inv['background_image']) ? '/EVENTINTELmayAPI/' . esc($inv['background_image']) : $templateImage ?>');
                background-size:cover;
                background-position:center;
            ">
            <h1 style="color:<?= esc($inv['theme_color']) ?>">
                <?= esc($inv['title']) ?>
            </h1>

            <p><?= nl2br(esc($inv['message'])) ?></p>

            <button class="btn">
                <?= esc($inv['button_text']) ?>
            </button>
        </div>
        <p>Guest link: 
            <a style="color:#f3c547" href="<?=$link?>"><?=$link?></a>
        </p>
    </div>
</div>

<script>
const preview = document.getElementById("preview");

const titleInput = document.querySelector('input[name="title"]');
const messageInput = document.querySelector('textarea[name="message"]');
const colorInput = document.querySelector('input[name="theme_color"]');
const fontSelect = document.querySelector('select[name="font_style"]');
const buttonInput = document.querySelector('input[name="button_text"]');
const bgInput = document.querySelector('input[name="background"]');
const template = document.getElementById("template");

const previewTitle = preview.querySelector("h1");
const previewMessage = preview.querySelector("p");
const previewButton = preview.querySelector("button");

const templateImages = {
    classic: "../images/classic.jpg",
    wedding: "../images/wedding.jpg",
    birthday: "../images/bday.jpg",
    corporate: "../images/corporate.jpg",
    elegant: "../images/elegant.jpg"
};

// LIVE TITLE
titleInput.addEventListener("input", () => {
    previewTitle.textContent = titleInput.value;
});

// LIVE MESSAGE
messageInput.addEventListener("input", () => {
    previewMessage.innerHTML = messageInput.value.replace(/\n/g,"<br>");
});

// LIVE COLOR
colorInput.addEventListener("input", () => {
    previewTitle.style.color = colorInput.value;
});

// LIVE FONT
fontSelect.addEventListener("change", () => {
    preview.style.fontFamily = fontSelect.value;
});

// LIVE BUTTON
buttonInput.addEventListener("input", () => {
    previewButton.textContent = buttonInput.value;
});

// TEMPLATE CHANGE
template.addEventListener("change", function () {

    const t = template.value.toLowerCase();

    preview.className = "preview " + t;

    preview.style.backgroundImage =
        "url('" + templateImages[t] + "')";

    preview.style.backgroundSize = "cover";
    preview.style.backgroundPosition = "center";
});

// CUSTOM BACKGROUND
bgInput.addEventListener("change", () => {

    const file = bgInput.files[0];

    if(!file) return;

    const reader = new FileReader();

    reader.onload = function(e){

        preview.style.backgroundImage =
        `linear-gradient(rgba(255,255,255,.35),rgba(255,255,255,.35)),
        url('${e.target.result}')`;

        preview.style.backgroundSize = "cover";
        preview.style.backgroundPosition = "center";
    }

    reader.readAsDataURL(file);

});

// LOAD DEFAULT TEMPLATE PAG OPEN NG PAGE
window.onload = () => {
    template.dispatchEvent(new Event("change"));
};
</script>

</body>
</html>
