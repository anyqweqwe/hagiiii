<!-- Common header for all pages -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'DMS - Debt Management System'; ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/themes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-VkzQbYkQ1Q0r5FRCGDpa2BkLomqKgJo0vvVuv5QW7HQwZ0pniSIF9vDOMkMt2g7x" crossorigin="anonymous">
    
    <!-- Theme management - Apply theme immediately to prevent flash -->
    <script src="js/theme.js"></script>
    
    <?php if (isset($extra_css)): ?>
    <style>
        <?php echo $extra_css; ?>
    </style>
    <?php endif; ?>
    
    <?php if (isset($extra_head)): ?>
    <?php echo $extra_head; ?>
    <?php endif; ?>
    
    <script src="js/scripts.js"></script>
    <script src="js/animations.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6DQD1KQ2U5hU5rKk5lZB6Y5n0hZl+6Q5F5e5F5F5F5F5" crossorigin="anonymous"></script>
    
    <?php if (isset($extra_scripts)): ?>
    <?php echo $extra_scripts; ?>
    <?php endif; ?>
</head>
<body> 