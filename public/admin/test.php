<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Test uploadu obrázku</title>
</head>

<body>
    <h2>Test uploadu obrázku (PHP endpoint)</h2>
    <form action="/admin/upload-image" method="post" enctype="multipart/form-data">
        <label for="file">Vyberte obrázek:</label>
        <input type="file" name="file" id="file" required>
        <button type="submit">Nahrát obrázek</button>
    </form>
</body>

</html>