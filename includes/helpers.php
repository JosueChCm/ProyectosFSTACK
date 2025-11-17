<?php
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function avatar_url(int $userId, ?string $avatar, ?string $updatedAt = null): string {
  if (!$avatar || $avatar === 'default.png') {
    return '/assets/img/avatar-default.png'; // pon aquí tu default real
  }
  $v = $updatedAt ? ('?v=' . urlencode((string)strtotime($updatedAt))) : '';
  return "/uploads/avatars/{$userId}/{$avatar}{$v}";
}
