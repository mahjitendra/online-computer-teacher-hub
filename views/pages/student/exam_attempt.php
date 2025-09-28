<?php require APPROOT . '/views/layouts/frontend/header.php'; ?>

<div class="exam-container">
    <h1><?php echo $data['exam']->title; ?></h1>
    <p>Time limit: <?php echo $data['exam']->duration; ?> minutes</p>

    <form action="<?php echo URLROOT; ?>/student/exams/submit/<?php echo $data['exam']->id; ?>" method="post">
        <?php foreach($data['questions'] as $index => $question): ?>
            <div class="question-block">
                <h4>Question <?php echo $index + 1; ?>: <?php echo $question->question_text; ?></h4>

                <?php if($question->question_type == 'multiple_choice'): ?>
                    <div class="options">
                        <?php foreach($question->options as $option): ?>
                            <div class="option">
                                <input type="radio" name="answers[<?php echo $question->id; ?>]" value="<?php echo $option->id; ?>">
                                <label><?php echo $option->option_text; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif($question->question_type == 'true_false'): ?>
                     <div class="options">
                        <div class="option">
                            <input type="radio" name="answers[<?php echo $question->id; ?>]" value="1">
                            <label>True</label>
                        </div>
                        <div class="option">
                            <input type="radio" name="answers[<?php echo $question->id; ?>]" value="0">
                            <label>False</label>
                        </div>
                    </div>
                <?php elseif($question->question_type == 'short_answer'): ?>
                    <div class="options">
                        <textarea name="answers[<?php echo $question->id; ?>]" class="form-control"></textarea>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <input type="submit" value="Submit Exam" class="btn-primary btn-lg">
    </form>
</div>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>