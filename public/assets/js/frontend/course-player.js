// Course video player functionality
class CoursePlayer {
    constructor(container) {
        this.container = container;
        this.video = container.querySelector('video');
        this.playButton = container.querySelector('.play-button');
        this.progressBar = container.querySelector('.progress-bar');
        this.progressFill = container.querySelector('.progress-fill');
        this.timeDisplay = container.querySelector('.time-display');
        this.volumeControl = container.querySelector('.volume-control');
        this.fullscreenButton = container.querySelector('.fullscreen-button');
        this.speedControl = container.querySelector('.speed-control');
        
        this.isPlaying = false;
        this.currentTime = 0;
        this.duration = 0;
        this.volume = 1;
        this.playbackRate = 1;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadProgress();
        this.updateTimeDisplay();
    }
    
    setupEventListeners() {
        // Play/Pause button
        this.playButton.addEventListener('click', () => {
            this.togglePlay();
        });
        
        // Video events
        this.video.addEventListener('loadedmetadata', () => {
            this.duration = this.video.duration;
            this.updateTimeDisplay();
        });
        
        this.video.addEventListener('timeupdate', () => {
            this.currentTime = this.video.currentTime;
            this.updateProgress();
            this.updateTimeDisplay();
            this.saveProgress();
        });
        
        this.video.addEventListener('ended', () => {
            this.onVideoEnded();
        });
        
        // Progress bar
        this.progressBar.addEventListener('click', (e) => {
            this.seekTo(e);
        });
        
        // Volume control
        if (this.volumeControl) {
            this.volumeControl.addEventListener('input', (e) => {
                this.setVolume(e.target.value / 100);
            });
        }
        
        // Speed control
        if (this.speedControl) {
            this.speedControl.addEventListener('change', (e) => {
                this.setPlaybackRate(parseFloat(e.target.value));
            });
        }
        
        // Fullscreen
        if (this.fullscreenButton) {
            this.fullscreenButton.addEventListener('click', () => {
                this.toggleFullscreen();
            });
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (this.container.contains(document.activeElement) || e.target === this.video) {
                this.handleKeyboard(e);
            }
        });
    }
    
    togglePlay() {
        if (this.isPlaying) {
            this.pause();
        } else {
            this.play();
        }
    }
    
    play() {
        this.video.play();
        this.isPlaying = true;
        this.playButton.innerHTML = '<i class="fas fa-pause"></i>';
        this.container.classList.add('playing');
    }
    
    pause() {
        this.video.pause();
        this.isPlaying = false;
        this.playButton.innerHTML = '<i class="fas fa-play"></i>';
        this.container.classList.remove('playing');
    }
    
    seekTo(e) {
        const rect = this.progressBar.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const percentage = clickX / rect.width;
        const newTime = percentage * this.duration;
        
        this.video.currentTime = newTime;
        this.currentTime = newTime;
        this.updateProgress();
    }
    
    updateProgress() {
        if (this.duration > 0) {
            const percentage = (this.currentTime / this.duration) * 100;
            this.progressFill.style.width = percentage + '%';
        }
    }
    
    updateTimeDisplay() {
        if (this.timeDisplay) {
            const current = this.formatTime(this.currentTime);
            const total = this.formatTime(this.duration);
            this.timeDisplay.textContent = `${current} / ${total}`;
        }
    }
    
    formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);
        
        if (hours > 0) {
            return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        } else {
            return `${minutes}:${secs.toString().padStart(2, '0')}`;
        }
    }
    
    setVolume(volume) {
        this.volume = Math.max(0, Math.min(1, volume));
        this.video.volume = this.volume;
        
        if (this.volumeControl) {
            this.volumeControl.value = this.volume * 100;
        }
    }
    
    setPlaybackRate(rate) {
        this.playbackRate = rate;
        this.video.playbackRate = rate;
    }
    
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            this.container.requestFullscreen().catch(err => {
                console.error('Error attempting to enable fullscreen:', err);
            });
        } else {
            document.exitFullscreen();
        }
    }
    
    handleKeyboard(e) {
        switch(e.code) {
            case 'Space':
                e.preventDefault();
                this.togglePlay();
                break;
            case 'ArrowLeft':
                e.preventDefault();
                this.video.currentTime = Math.max(0, this.video.currentTime - 10);
                break;
            case 'ArrowRight':
                e.preventDefault();
                this.video.currentTime = Math.min(this.duration, this.video.currentTime + 10);
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.setVolume(this.volume + 0.1);
                break;
            case 'ArrowDown':
                e.preventDefault();
                this.setVolume(this.volume - 0.1);
                break;
            case 'KeyF':
                e.preventDefault();
                this.toggleFullscreen();
                break;
        }
    }
    
    loadProgress() {
        const tutorialId = this.container.dataset.tutorialId;
        if (tutorialId) {
            const savedProgress = localStorage.getItem(`tutorial_progress_${tutorialId}`);
            if (savedProgress) {
                const progress = JSON.parse(savedProgress);
                this.video.currentTime = progress.currentTime || 0;
            }
        }
    }
    
    saveProgress() {
        const tutorialId = this.container.dataset.tutorialId;
        if (tutorialId) {
            const progress = {
                currentTime: this.currentTime,
                duration: this.duration,
                percentage: (this.currentTime / this.duration) * 100,
                lastWatched: new Date().toISOString()
            };
            
            localStorage.setItem(`tutorial_progress_${tutorialId}`, JSON.stringify(progress));
            
            // Send progress to server every 30 seconds
            if (Math.floor(this.currentTime) % 30 === 0) {
                this.syncProgressToServer(tutorialId, progress);
            }
        }
    }
    
    syncProgressToServer(tutorialId, progress) {
        fetch(`/api/v1/tutorials/${tutorialId}/progress`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(progress)
        }).catch(error => {
            console.error('Error syncing progress:', error);
        });
    }
    
    onVideoEnded() {
        this.isPlaying = false;
        this.playButton.innerHTML = '<i class="fas fa-replay"></i>';
        
        // Mark tutorial as completed
        const tutorialId = this.container.dataset.tutorialId;
        if (tutorialId) {
            this.markTutorialCompleted(tutorialId);
        }
        
        // Show next tutorial suggestion
        this.showNextTutorial();
    }
    
    markTutorialCompleted(tutorialId) {
        fetch(`/tutorials/complete/${tutorialId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Tutorial completed!', 'success');
            }
        })
        .catch(error => {
            console.error('Error marking tutorial as completed:', error);
        });
    }
    
    showNextTutorial() {
        const nextTutorialUrl = this.container.dataset.nextTutorial;
        if (nextTutorialUrl) {
            const modal = document.createElement('div');
            modal.className = 'modal active';
            modal.innerHTML = `
                <div class="modal-content">
                    <h3>Tutorial Completed!</h3>
                    <p>Great job! Ready for the next tutorial?</p>
                    <div class="modal-actions">
                        <button class="btn btn-secondary modal-close">Stay Here</button>
                        <a href="${nextTutorialUrl}" class="btn btn-primary">Next Tutorial</a>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Close modal functionality
            modal.querySelector('.modal-close').addEventListener('click', () => {
                modal.remove();
            });
        }
    }
}

// Initialize video players
document.addEventListener('DOMContentLoaded', function() {
    const videoContainers = document.querySelectorAll('.video-player-container');
    videoContainers.forEach(container => {
        new CoursePlayer(container);
    });
});

// Note-taking functionality
class NoteTaker {
    constructor() {
        this.notes = [];
        this.currentVideoTime = 0;
        this.init();
    }
    
    init() {
        this.setupNoteInterface();
        this.loadNotes();
    }
    
    setupNoteInterface() {
        const noteContainer = document.querySelector('.notes-container');
        if (!noteContainer) return;
        
        const noteForm = noteContainer.querySelector('.note-form');
        const notesList = noteContainer.querySelector('.notes-list');
        
        if (noteForm) {
            noteForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.addNote();
            });
        }
    }
    
    addNote() {
        const noteText = document.querySelector('.note-input').value.trim();
        if (!noteText) return;
        
        const note = {
            id: Date.now(),
            text: noteText,
            timestamp: this.currentVideoTime,
            created: new Date().toISOString()
        };
        
        this.notes.push(note);
        this.saveNotes();
        this.renderNotes();
        
        // Clear input
        document.querySelector('.note-input').value = '';
    }
    
    deleteNote(noteId) {
        this.notes = this.notes.filter(note => note.id !== noteId);
        this.saveNotes();
        this.renderNotes();
    }
    
    renderNotes() {
        const notesList = document.querySelector('.notes-list');
        if (!notesList) return;
        
        notesList.innerHTML = this.notes.map(note => `
            <div class="note-item" data-note-id="${note.id}">
                <div class="note-timestamp">${this.formatTime(note.timestamp)}</div>
                <div class="note-text">${note.text}</div>
                <div class="note-actions">
                    <button class="btn-small" onclick="noteTaker.jumpToTime(${note.timestamp})">
                        <i class="fas fa-play"></i>
                    </button>
                    <button class="btn-small btn-danger" onclick="noteTaker.deleteNote(${note.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }
    
    jumpToTime(timestamp) {
        const video = document.querySelector('video');
        if (video) {
            video.currentTime = timestamp;
        }
    }
    
    formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${minutes}:${secs.toString().padStart(2, '0')}`;
    }
    
    saveNotes() {
        const tutorialId = document.querySelector('.video-player-container').dataset.tutorialId;
        if (tutorialId) {
            localStorage.setItem(`notes_${tutorialId}`, JSON.stringify(this.notes));
        }
    }
    
    loadNotes() {
        const tutorialId = document.querySelector('.video-player-container').dataset.tutorialId;
        if (tutorialId) {
            const savedNotes = localStorage.getItem(`notes_${tutorialId}`);
            if (savedNotes) {
                this.notes = JSON.parse(savedNotes);
                this.renderNotes();
            }
        }
    }
}

// Initialize note taker
let noteTaker;
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.notes-container')) {
        noteTaker = new NoteTaker();
    }
});