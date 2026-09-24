<?php
/**
 * Bootstrap pagination partial.
 * @var array $pagination {total,page,per_page,last_page}
 */
if (empty($pagination) || $pagination['last_page'] <= 1) {
    return;
}

$key = $paginationKey ?? 'page';
$baseUrl = $paginationBase ?? (strtok($_SERVER['REQUEST_URI'], '?') ?: '/');
$query = $paginationQuery ?? $_GET;
unset($query[$key]);
$queryString = http_build_query($query);
?>
<nav aria-label="Page navigation">
    <ul class="pagination pagination-sm mb-0">
        <?php
        $page = (int)$pagination['page'];
        $last = (int)$pagination['last_page'];

        $urlFor = function (int $p) use ($baseUrl, $queryString, $key): string {
            return $baseUrl . '?' . ($queryString !== '' ? $queryString . '&' : '') . $key . '=' . $p;
        };
        ?>
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e($urlFor($page - 1)) ?>">&laquo;</a>
        </li>

        <?php for ($p = max(1, $page - 2); $p <= min($last, $page + 2); $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= e($urlFor($p)) ?>"><?= (int)$p ?></a>
            </li>
        <?php endfor; ?>

        <li class="page-item <?= $page >= $last ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e($urlFor($page + 1)) ?>">&raquo;</a>
        </li>
    </ul>
</nav>