<h1>Seznam uživatelů</h1>
<a href="?controller=Admin&action=dashboard">Zpět</a>
<a href="?controller=User&action=create">Vytvořit nového uživatele</a>
<table>
    <tr>
        <th>ID</th>
        <th>Jméno</th>
        <th>Email</th>
        <th>Akce</th>
        <th>Role</th>
        <th>Články</th>
        <th>Zhlédnutí</th>
    </tr>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['name']) ?> <?= htmlspecialchars($user['surname']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
                <a href="?controller=User&action=delete&id=<?= $user['id'] ?>" onclick="return confirm('Are you sure?')">Smazat</a>
                <a href="?controller=User&action=edit&id=<?= $user['id'] ?>">Upravit</a>
                <a href="#">Zobrazit</a>
            </td>
            <td>
                <?php
                if ($user['role'] == 3) {
                    echo '<div><span style="color: #f00;">' . $user['role'] . '<i class="fa-solid fa-user-graduate"></i></span></div=>';
                } elseif ($user['role'] == 2) {
                    echo '<div><span style="color: #0f0;">' . $user['role'] . '<i class="fa-solid fa-skull-crossbones"></i></span></div>';
                } elseif ($user['role'] == 1) {
                    echo '<div><span style="color: #00f;">' . $user['role'] . '<i class="fa-solid fa-skull"></i></span></div>';
                } else {
                    echo '<div><span style="color: #ff0d25;">' . $user['role'] . '<i class="fa-solid fa-user"></i></span></div>';
                }
                ?>
            </td>
            <td><?= htmlspecialchars($user['soucet']) ?></td>
            <td><?= htmlspecialchars($user['zhlednuti'] == 0 ? '0' : $user['zhlednuti']); ?>
        </tr>
    <?php endforeach; ?>
</table>