<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/admin">Admin Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item"><a class="nav-link" href="/admin/articles">Články</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/categories">Kategorie</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/statistics">Statistiky</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/users">Uživatelé</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/access-control">Správa přístupů</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if ($isLoggedIn): ?>
                    <ul class="navbar-nav ms-auto">
                        <?php if (isset($_SESSION['email'])): ?>
                            <li class="nav-item">
                                <span class="nav-link"><?= htmlspecialchars($_SESSION['email']) ?></span>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a style="text-decoration: underline;" class="nav-link text-danger" href="/admin/logout">Odhlásit se</a>
                        </li>
                    </ul>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>