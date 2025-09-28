<?php require APPROOT . '/views/layouts/frontend/header.php'; ?>

<div class="teacher-dashboard-container">
    <div class="exam-header">
        <h1><?php echo $data['exam']->title; ?></h1>
        <p><?php echo $data['exam']->description; ?></p>
    </div>

    <hr>

    <div class="questions-list">
        <h2>Questions</h2>
        <?php if(empty($data['questions'])): ?>
            <p>No questions have been added to this exam yet.</p>
        <?php else: ?>
            <ul>
                <?php foreach($data['questions'] as $question): ?>
                    <li>
                        <strong><?php echo $question->question_text; ?></strong> (<?php echo $question->question_type; ?>)
                        <?php if($question->question_type == 'multiple_choice' && !empty($question->options)): ?>
                            <ul>
                                <?php foreach($question->options as $option): ?>
                                    <li class="<?php echo $option->is_correct ? 'correct-answer' : ''; ?>">
                                        <?php echo $option->option_text; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <hr>

    <div class="add-question-form">
        <h3>Add New Question</h3>
        <form action="<?php echo URLROOT; ?>/teacher/questions/add/<?php echo $data['exam']->id; ?>" method="post">
            <div class="form-group">
                <label for="question_text">Question:</label>
                <textarea name="question_text" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label for="question_type">Question Type:</label>
                <select name="question_type" class="form-control" id="question_type_selector">
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="true_false">True/False</option>
                    <option value="short_answer">Short Answer</option>
                </select>
            </div>

            <div id="mc_options_container">
                <label>Options:</label>
                <div class="option-group">
                    <input type="text" name="options[0][text]" placeholder="Option 1">
                    <input type="radio" name="correct_option" value="0"> Correct
                </div>
                 <div class="option-group">
                    <input type="text" name="options[1][text]" placeholder="Option 2">
                    <input type="radio" name="correct_option" value="1"> Correct
                </div>
                 <div class="option-group">
                    <input type="text" name="options[2][text]" placeholder="Option 3">
                    <input type="radio" name="correct_option" value="2"> Correct
                </div>
                 <div class="option-group">
                    <input type="text" name="options[3][text]" placeholder="Option 4">
                    <input type="radio" name="correct_option" value="3"> Correct
                </div>
            </div>

            <input type="submit" value="Add Question" class="btn-primary">
        </form>
    </div>
</div>

<script>
    // Simple script to hide/show MC options
    document.getElementById('question_type_selector').addEventListener('change', function(){
        var container = document.getElementById('mc_options_container');
        if(this.value === 'multiple_choice'){
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    });
</script>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>