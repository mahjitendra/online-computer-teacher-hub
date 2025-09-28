// Exam system functionality
class ExamSystem {
    constructor() {
        this.timeRemaining = 0;
        this.timerInterval = null;
        this.answers = {};
        this.isSubmitted = false;
        this.autoSaveInterval = null;
        this.warningShown = false;
        
        this.init();
    }
    
    init() {
        this.setupTimer();
        this.setupQuestionNavigation();
        this.setupAutoSave();
        this.setupAnswerTracking();
        this.setupSubmitButton();
        this.setupWarnings();
        this.preventCheating();
    }
    
    setupTimer() {
        const timerElement = document.querySelector('.exam-timer');
        if (!timerElement) return;
        
        this.timeRemaining = parseInt(timerElement.dataset.duration) * 60; // Convert minutes to seconds
        
        this.timerInterval = setInterval(() => {
            this.updateTimer();
        }, 1000);
        
        this.updateTimer();
    }
    
    updateTimer() {
        const timerElement = document.querySelector('.exam-timer');
        if (!timerElement) return;
        
        if (this.timeRemaining <= 0) {
            this.timeUp();
            return;
        }
        
        const hours = Math.floor(this.timeRemaining / 3600);
        const minutes = Math.floor((this.timeRemaining % 3600) / 60);
        const seconds = this.timeRemaining % 60;
        
        let timeString = '';
        if (hours > 0) {
            timeString = `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        } else {
            timeString = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }
        
        timerElement.textContent = timeString;
        
        // Warning when 5 minutes left
        if (this.timeRemaining === 300 && !this.warningShown) {
            this.showTimeWarning();
            this.warningShown = true;
        }
        
        // Change color when time is running out
        if (this.timeRemaining <= 300) {
            timerElement.classList.add('warning');
        }
        if (this.timeRemaining <= 60) {
            timerElement.classList.add('critical');
        }
        
        this.timeRemaining--;
    }
    
    timeUp() {
        clearInterval(this.timerInterval);
        showNotification('Time is up! Submitting your exam...', 'warning');
        this.submitExam(true);
    }
    
    showTimeWarning() {
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.innerHTML = `
            <div class="modal-content">
                <h3>⚠️ Time Warning</h3>
                <p>You have 5 minutes remaining to complete the exam.</p>
                <button class="btn btn-primary modal-close">Continue</button>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        modal.querySelector('.modal-close').addEventListener('click', () => {
            modal.remove();
        });
        
        setTimeout(() => {
            if (modal.parentNode) {
                modal.remove();
            }
        }, 5000);
    }
    
    setupQuestionNavigation() {
        const questionNumbers = document.querySelectorAll('.question-number');
        const questions = document.querySelectorAll('.question-block');
        
        questionNumbers.forEach((number, index) => {
            number.addEventListener('click', () => {
                this.showQuestion(index);
                this.updateQuestionNavigation();
            });
        });
        
        // Next/Previous buttons
        const nextButtons = document.querySelectorAll('.next-question');
        const prevButtons = document.querySelectorAll('.prev-question');
        
        nextButtons.forEach(button => {
            button.addEventListener('click', () => {
                const currentIndex = this.getCurrentQuestionIndex();
                if (currentIndex < questions.length - 1) {
                    this.showQuestion(currentIndex + 1);
                    this.updateQuestionNavigation();
                }
            });
        });
        
        prevButtons.forEach(button => {
            button.addEventListener('click', () => {
                const currentIndex = this.getCurrentQuestionIndex();
                if (currentIndex > 0) {
                    this.showQuestion(currentIndex - 1);
                    this.updateQuestionNavigation();
                }
            });
        });
    }
    
    showQuestion(index) {
        const questions = document.querySelectorAll('.question-block');
        questions.forEach((question, i) => {
            question.style.display = i === index ? 'block' : 'none';
        });
    }
    
    getCurrentQuestionIndex() {
        const questions = document.querySelectorAll('.question-block');
        for (let i = 0; i < questions.length; i++) {
            if (questions[i].style.display !== 'none') {
                return i;
            }
        }
        return 0;
    }
    
    updateQuestionNavigation() {
        const questionNumbers = document.querySelectorAll('.question-number');
        const currentIndex = this.getCurrentQuestionIndex();
        
        questionNumbers.forEach((number, index) => {
            number.classList.toggle('current', index === currentIndex);
            
            // Mark as answered if question has an answer
            const questionId = number.dataset.questionId;
            if (this.answers[questionId]) {
                number.classList.add('answered');
            }
        });
    }
    
    setupAnswerTracking() {
        const inputs = document.querySelectorAll('input[type="radio"], input[type="checkbox"], textarea');
        
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                this.saveAnswer(input);
                this.updateQuestionNavigation();
            });
        });
    }
    
    saveAnswer(input) {
        const questionId = input.name.replace('answers[', '').replace(']', '');
        
        if (input.type === 'radio') {
            this.answers[questionId] = input.value;
        } else if (input.type === 'checkbox') {
            if (!this.answers[questionId]) {
                this.answers[questionId] = [];
            }
            if (input.checked) {
                this.answers[questionId].push(input.value);
            } else {
                this.answers[questionId] = this.answers[questionId].filter(val => val !== input.value);
            }
        } else if (input.tagName === 'TEXTAREA') {
            this.answers[questionId] = input.value;
        }
        
        // Save to localStorage
        this.saveToLocalStorage();
    }
    
    setupAutoSave() {
        this.autoSaveInterval = setInterval(() => {
            this.autoSave();
        }, 30000); // Auto-save every 30 seconds
        
        // Load saved answers
        this.loadFromLocalStorage();
    }
    
    autoSave() {
        if (this.isSubmitted) return;
        
        const examId = document.querySelector('form').dataset.examId;
        if (!examId) return;
        
        fetch(`/api/v1/exams/${examId}/auto-save`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                answers: this.answers,
                timeRemaining: this.timeRemaining
            })
        }).catch(error => {
            console.error('Auto-save failed:', error);
        });
    }
    
    saveToLocalStorage() {
        const examId = document.querySelector('form').dataset.examId;
        if (examId) {
            localStorage.setItem(`exam_answers_${examId}`, JSON.stringify(this.answers));
        }
    }
    
    loadFromLocalStorage() {
        const examId = document.querySelector('form').dataset.examId;
        if (examId) {
            const savedAnswers = localStorage.getItem(`exam_answers_${examId}`);
            if (savedAnswers) {
                this.answers = JSON.parse(savedAnswers);
                this.restoreAnswers();
            }
        }
    }
    
    restoreAnswers() {
        Object.keys(this.answers).forEach(questionId => {
            const answer = this.answers[questionId];
            
            if (Array.isArray(answer)) {
                // Checkbox answers
                answer.forEach(value => {
                    const checkbox = document.querySelector(`input[name="answers[${questionId}]"][value="${value}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            } else {
                // Radio or textarea answers
                const input = document.querySelector(`input[name="answers[${questionId}]"][value="${answer}"]`) ||
                             document.querySelector(`textarea[name="answers[${questionId}]"]`);
                if (input) {
                    if (input.type === 'radio') {
                        input.checked = true;
                    } else {
                        input.value = answer;
                    }
                }
            }
        });
        
        this.updateQuestionNavigation();
    }
    
    setupSubmitButton() {
        const submitButton = document.querySelector('.submit-exam');
        const form = document.querySelector('form');
        
        if (submitButton && form) {
            submitButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.confirmSubmit();
            });
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitExam();
            });
        }
    }
    
    confirmSubmit() {
        const totalQuestions = document.querySelectorAll('.question-block').length;
        const answeredQuestions = Object.keys(this.answers).length;
        const unansweredCount = totalQuestions - answeredQuestions;
        
        let message = 'Are you sure you want to submit your exam?';
        if (unansweredCount > 0) {
            message += `\n\nYou have ${unansweredCount} unanswered question(s).`;
        }
        
        if (confirm(message)) {
            this.submitExam();
        }
    }
    
    submitExam(timeUp = false) {
        if (this.isSubmitted) return;
        
        this.isSubmitted = true;
        clearInterval(this.timerInterval);
        clearInterval(this.autoSaveInterval);
        
        const form = document.querySelector('form');
        const submitButton = document.querySelector('.submit-exam');
        
        if (submitButton) {
            submitButton.textContent = 'Submitting...';
            submitButton.disabled = true;
        }
        
        // Add answers to form
        Object.keys(this.answers).forEach(questionId => {
            const answer = this.answers[questionId];
            
            if (Array.isArray(answer)) {
                answer.forEach(value => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `answers[${questionId}][]`;
                    input.value = value;
                    form.appendChild(input);
                });
            } else {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `answers[${questionId}]`;
                input.value = answer;
                form.appendChild(input);
            }
        });
        
        // Add time up flag if applicable
        if (timeUp) {
            const timeUpInput = document.createElement('input');
            timeUpInput.type = 'hidden';
            timeUpInput.name = 'time_up';
            timeUpInput.value = '1';
            form.appendChild(timeUpInput);
        }
        
        // Clear localStorage
        const examId = form.dataset.examId;
        if (examId) {
            localStorage.removeItem(`exam_answers_${examId}`);
        }
        
        form.submit();
    }
    
    setupWarnings() {
        // Warn before leaving page
        window.addEventListener('beforeunload', (e) => {
            if (!this.isSubmitted) {
                e.preventDefault();
                e.returnValue = 'Are you sure you want to leave? Your progress will be lost.';
                return e.returnValue;
            }
        });
        
        // Detect tab switching
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && !this.isSubmitted) {
                console.warn('Tab switched during exam');
                // Could implement stricter measures here
            }
        });
    }
    
    preventCheating() {
        // Disable right-click
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
        });
        
        // Disable text selection
        document.addEventListener('selectstart', (e) => {
            e.preventDefault();
        });
        
        // Disable copy/paste
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && (e.key === 'c' || e.key === 'v' || e.key === 'a' || e.key === 'x')) {
                e.preventDefault();
            }
            
            // Disable F12, Ctrl+Shift+I, etc.
            if (e.key === 'F12' || 
                (e.ctrlKey && e.shiftKey && e.key === 'I') ||
                (e.ctrlKey && e.shiftKey && e.key === 'C') ||
                (e.ctrlKey && e.key === 'U')) {
                e.preventDefault();
            }
        });
        
        // Detect developer tools
        let devtools = {
            open: false,
            orientation: null
        };
        
        const threshold = 160;
        
        setInterval(() => {
            if (window.outerHeight - window.innerHeight > threshold || 
                window.outerWidth - window.innerWidth > threshold) {
                if (!devtools.open) {
                    devtools.open = true;
                    console.warn('Developer tools detected');
                    showNotification('Developer tools detected. This may be considered cheating.', 'warning');
                }
            } else {
                devtools.open = false;
            }
        }, 500);
    }
}

// Initialize exam system
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.exam-container')) {
        new ExamSystem();
    }
});

// Exam review functionality
class ExamReview {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupReviewNavigation();
        this.highlightAnswers();
    }
    
    setupReviewNavigation() {
        const reviewButtons = document.querySelectorAll('.review-question');
        
        reviewButtons.forEach(button => {
            button.addEventListener('click', () => {
                const questionId = button.dataset.questionId;
                this.scrollToQuestion(questionId);
            });
        });
    }
    
    scrollToQuestion(questionId) {
        const question = document.querySelector(`[data-question-id="${questionId}"]`);
        if (question) {
            question.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    
    highlightAnswers() {
        const correctAnswers = document.querySelectorAll('.correct-answer');
        const incorrectAnswers = document.querySelectorAll('.incorrect-answer');
        const userAnswers = document.querySelectorAll('.user-answer');
        
        correctAnswers.forEach(answer => {
            answer.style.backgroundColor = '#d4edda';
            answer.style.border = '2px solid #28a745';
        });
        
        incorrectAnswers.forEach(answer => {
            answer.style.backgroundColor = '#f8d7da';
            answer.style.border = '2px solid #dc3545';
        });
        
        userAnswers.forEach(answer => {
            answer.style.backgroundColor = '#fff3cd';
            answer.style.border = '2px solid #ffc107';
        });
    }
}

// Initialize exam review
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.exam-review')) {
        new ExamReview();
    }
});