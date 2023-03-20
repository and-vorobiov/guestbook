<?php
// TODO 1: PREPARING ENVIRONMENT: 1) session 2) functions
session_start();

// Function to validate form fields
function validate_fields($email, $name, $text) {
    $errors = [];

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($name) || strlen($name) < 2) {
        $errors['name'] = 'Please enter a valid name.';
    }

    if (empty($text) || strlen($text) < 5) {
        $errors['text'] = 'Please enter a valid comment (minimum 5 characters).';
    }

    return $errors;
}


// Function to render guestbook comments with pagination
function render_guestbook_comments($file, $page, $items_per_page) {
    $comments = [];

    if (($handle = fopen($file, "r")) !== FALSE) {
        $offset = ($page - 1) * $items_per_page;
        $count = 0;
        $current_item = 0;

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($current_item >= $offset && $count < $items_per_page) {
                $comments[] = [
                    'email' => $data[0],
                    'name'  => $data[1],
                    'text'  => $data[2],
                ];
                $count++;
            }
            $current_item++;
        }
        fclose($handle);
    }

    return $comments;
}

                        // Pagination variables
                        $items_per_page = 5;
                        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                        if ($page < 1) {
                        $page = 1;
                        }

                        // Retrieve comments with pagination
                        $comments = render_guestbook_comments('comments.csv', $page, $items_per_page);

                        // Calculate the total number of pages
                        $total_comments = count(file('comments.csv'));
                        $total_pages = ceil($total_comments / $items_per_page);

// TODO 2: ROUTING
// No routing required for this simple script

// TODO 3: CODE by REQUEST METHODS (ACTIONS) GET, POST, etc. (handle data from request): 1) validate 2) working with data source 3) transforming data
$errors = [];
$email = '';
$name = '';
$text = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $name  = isset($_POST['name'])  ? trim($_POST['name'])  : '';
    $text  = isset($_POST['text'])  ? trim($_POST['text'])  : '';

    $errors = validate_fields($email, $name, $text);

    if (empty($errors)) {
        $file = 'comments.csv';
        $handle = fopen($file, 'a');
        fputcsv($handle, [$email, $name, $text]);
        fclose($handle);
    }
}


// TODO 4: RENDER: 1) view (html) 2) data (from php)
$comments = render_guestbook_comments('comments.csv', $page, $items_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once 'sectionHead.php'; ?>
<body>
<div class="container">
    <!-- navbar menu -->
    <?php require_once 'sectionNavbar.php'; ?>
    <br>
    <!-- guestbook section -->
    <div class="card card-primary">
        <div class="card-header bg-primary text-light">
            GuestBook form
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <!-- GuestBook html form -->
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php
                            echo htmlspecialchars($email); ?>">
                            <?php if (isset($errors['email'])): ?>
                                <div class="alert alert-danger mt-2">
                                    <?php echo $errors['email']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
                            <?php if (isset($errors['name'])): ?>
                                <div class="alert alert-danger mt-2">
                                    <?php echo $errors['name']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="text">Comment:</label>
                            <textarea class="form-control" id="text" name="text" rows="3"><?php echo htmlspecialchars($text); ?></textarea>
                            <?php if (isset($errors['text'])): ?>
                                <div class="alert alert-danger mt-2">
                                    <?php echo $errors['text']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="card card-primary">
        <div class="card-header bg-body-secondary text-dark">
            Comments
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <!-- Render guestBook comments -->
                    <?php foreach ($comments as $comment): ?>
                        <div class="card mt-3">
                            <div class="card-header">
                                <?php echo htmlspecialchars($comment['name']); ?> (<?php echo htmlspecialchars($comment['email']); ?>)
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['text'])); ?></p>
                            </div>
                        </div>




                        <!-- Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                            </ul>
                        </nav>



                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>