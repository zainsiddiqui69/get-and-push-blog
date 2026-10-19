<?php
// Site settings – edit these values
$site_name = 'Get & Push';
$site_title = $site_name . ' – Business Insights & Company News';
$site_desc = $site_name . ' publishes practical business articles, company news and ideas for growing teams.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?php echo htmlspecialchars($site_title); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($site_desc); ?>">
<script src="https://cdn.tailwindcss.com/3.4.17"></script>
<script src="https://cdn.jsdelivr.net/npm/react@18.3.1/umd/react.production.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/react-dom@18.3.1/umd/react-dom.production.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/htm@3.1.1/dist/htm.umd.js"></script>
<link rel="stylesheet" href="styles.css?v=<?php echo @filemtime(__DIR__.'/styles.css'); ?>">
<script src="tailwind-config.js"></script>
</head>
<body>
<div id="root"></div>
<script src="app.js?v=<?php echo @filemtime(__DIR__.'/app.js'); ?>"></script>
</body>
</html>
