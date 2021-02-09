<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= url("admin/css/monkey.css") ?>">
    <title>Monkey Administration</title>
</head>
<body class="fullscreen">
    <h1>Monkey</h1>
    <form action="<?= router("m_guard_attempt") ?>" method="POST"  class="f-col align-center">
        <section class="f-col mb-2 mt-2 align-center">
        <span>Please type the Admin Password</span>
        <input type="password" class="spaced" name="password" >
        </section>
        <input type="submit" class="button green" value="Enter">
    </form>
</body>
</html>