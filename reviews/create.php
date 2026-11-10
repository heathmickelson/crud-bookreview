<?php
    require_once '../includes/header.php'
?>

<main>
    <label for="book_id" class="form-label">
        Select a Book
    </label>

    <select
        class="form-select mb-3"
        id="book_id"
        name="book_id"
        required
    >
        <option value="" selected disabled>
            Choose a book
        </option>

        <!-- Dynamically populate books from database -->
    </select>
    <div class="container mx-auto">
        <form action="#submitted" method='POST'>
            <label class="mb-3 form-label" for="bTitle">Book Title</label>
            <input class="mb-3 form-control" type="text" id="bTitle" name="bTitle">
            <label class="mb-3 form-label" for="rating">Rating</label>
            <<input
                class="form-control mb-3"
                type="number"
                id="rating"
                name="rating"
                min="1"
                max="5"
                step="1"
                required
            >
            <label class="form-label d-block" for="rText">Review</label>
            <textarea
                class="form-control"
                name="rText"
                id="rText"
                rows="6"
                placeholder="What did you think about this book?"
                required
            ></textarea>
            <div class="d-flex justify-content-center mt-3 mb-3">
                <input class="btn btn-primary" type="submit" value="Submit">
            </div>
        </form>
    </div>
</main>

<?php
    require_once '../includes/footer.php'
?>