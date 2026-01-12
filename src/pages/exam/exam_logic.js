// src/pages/exam/exam_logic.js

let currentQuestionIndex = 0;
let timeLeft = 30; // seconds
let timerInterval;
let mediaRecorder;
let audioChunks = [];
let isRecording = false;
let isPrepTime = false;

// UI Elements
const questionArea = document.getElementById('question-area');
const overlay = document.getElementById('overlay');
const overlayText = document.getElementById('overlay-text');
const countdownEl = document.getElementById('countdown');
const timerText = document.getElementById('timer-text');
const timerRing = document.getElementById('timer-ring');
const actionBtn = document.getElementById('action-btn');
const recIcon = document.getElementById('rec-icon');
const qCurrent = document.getElementById('q-current');
const notepadArea = document.getElementById('notepad-area');
const logicSidebar = document.getElementById('logic-sidebar');
const forPoints = document.getElementById('for-points');
const againstPoints = document.getElementById('against-points');

// Circle Params
const radius = 40;
const circumference = 2 * Math.PI * radius;
timerRing.style.strokeDasharray = `${circumference} ${circumference}`;

function setProgress(percent) {
    const offset = circumference - (percent / 100) * circumference;
    timerRing.style.strokeDashoffset = offset;
}

function init() {
    // Check permission immediately
    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            setupRecorder(stream);
            startQuestionSequence();
        })
        .catch(err => {
            alert("Microphone access is required. Please enable it.");
        });
}

function setupRecorder(stream) {
    mediaRecorder = new MediaRecorder(stream);

    mediaRecorder.ondataavailable = event => {
        audioChunks.push(event.data);
    };

    mediaRecorder.onstop = () => {
        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
        uploadAudio(audioBlob);
        audioChunks = [];
    };
}

async function uploadAudio(blob) {
    // Show uploading state if needed
    const formData = new FormData();
    formData.append('audio', blob, 'recording.webm');
    formData.append('submission_id', submissionId);
    formData.append('question_id', questions[currentQuestionIndex].id);

    try {
        await fetch('../../api/upload_audio.php', {
            method: 'POST',
            body: formData
        });
        // Proceed to next question only after upload initiated (or completed for strictness)
        nextQuestion();
    } catch (e) {
        console.error("Upload failed", e);
        // Retry logic could go here
        nextQuestion();
    }
}

function startQuestionSequence() {
    loadQuestion(currentQuestionIndex);
}

function loadQuestion(index) {
    if (index >= questions.length) {
        window.location.href = nextPartUrl;
        return;
    }

    qCurrent.innerText = index + 1;
    const q = questions[index];
    let content = q.content;
    let image = q.media_url;

    // Reset UI
    questionArea.innerHTML = '';
    notepadArea.classList.add('hidden');
    logicSidebar.classList.add('hidden');

    // Parse JSON content if needed
    try {
        const parsed = JSON.parse(content);
        if (typeof parsed === 'object') content = parsed;
    } catch(e) {}

    // Determine UI based on Part
    if (currentPart === '1.1') {
        // Simple Text
        questionArea.innerHTML = `<h2 class="text-3xl font-bold mb-4">${typeof content === 'string' ? content : content[0]}</h2>`;
        timeLeft = 30;
        isPrepTime = false;
    }
    else if (currentPart === '1.2') {
        // Image + Text
        // UPDATED: Now supports distinct questions, displaying just the content string
        const prompt = Array.isArray(content) ? content[0] : content;

        let imgHtml = '';
        if (image) {
            // Adjust path: strictly relative from browser perspective
            // runner.php is in src/pages/exam
            // images in uploads/images (root/uploads/images) or assets (root/assets)
            // But wait, seed said 'assets/images/...'
            // We need to resolve path. Assuming 'assets' means from root.
            // Relative from src/pages/exam -> ../../../
            imgHtml = `<img src="../../../${image}" class="h-64 mx-auto rounded-lg shadow-lg mb-4 object-contain">`;
        }

        questionArea.innerHTML = `
            ${imgHtml}
            <h2 class="text-2xl font-bold mb-4">${prompt}</h2>
        `;
        timeLeft = 30;
        isPrepTime = false;
    }
    else if (currentPart === '2') {
        // Part 2: Prep + Monologue
        // Logic: 1 min prep (notepad) -> 2 min record
        if (!isPrepTime) {
            // Start Prep Phase
            isPrepTime = true;
            timeLeft = 60; // 1 min prep

            questionArea.innerHTML = `<h2 class="text-3xl font-bold mb-4">Topic: ${content}</h2>`;
            if (image) {
                questionArea.innerHTML += `<img src="../../../${image}" class="h-48 mx-auto rounded-lg shadow-lg mb-4 object-contain">`;
            }

            notepadArea.classList.remove('hidden');

            // Visual indicator for Prep
            actionBtn.classList.add('opacity-50', 'cursor-not-allowed');
            recIcon.className = "w-8 h-8 bg-yellow-400 rounded-full"; // Yellow for prep

            startTimer(() => {
                // End Prep, Start Recording
                isPrepTime = false;
                timeLeft = 120; // 2 min
                notepadArea.classList.add('hidden'); // Discard notes
                actionBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                startRecordingPhase();
            });
            return; // Exit here, timer handles transition
        }
    }
    else if (currentPart === '3') {
        // C1: Debate
        // Content is object {topic, for_prompts[], against_prompts[]}
        const topic = content.topic || "Debate Topic";
        const fors = content.for_prompts || [];
        const againsts = content.against_prompts || [];

        questionArea.innerHTML = `<h2 class="text-4xl font-bold mb-8">${topic}</h2>`;

        // Populate Sidebar
        forPoints.innerHTML = fors.map(p => `<li>${p}</li>`).join('');
        againstPoints.innerHTML = againsts.map(p => `<li>${p}</li>`).join('');
        logicSidebar.classList.remove('hidden');
        questionArea.classList.add('mr-64'); // Make room for sidebar

        timeLeft = 120; // Standard C1 time? Req said "Logic: Students must provide arguments...". Assuming 2 min.
        isPrepTime = false;
    }

    startRecordingPhase();
}

function startRecordingPhase() {
    // Visual Countdown Overlay
    overlay.classList.remove('hidden');
    let count = 3;
    countdownEl.innerText = count;
    overlayText.innerText = isPrepTime ? "Preparation Starts In..." : "Recording Starts In...";

    const countInt = setInterval(() => {
        count--;
        if (count > 0) {
            countdownEl.innerText = count;
        } else {
            clearInterval(countInt);
            overlay.classList.add('hidden');
            startTimer(nextQuestion); // Timer ends -> Next
            if (!isPrepTime) {
                startRecording();
            }
        }
    }, 1000);
}

function startRecording() {
    isRecording = true;
    audioChunks = [];
    mediaRecorder.start();

    // UI Update
    recIcon.className = "w-8 h-8 bg-white rounded-sm"; // Stop square
    actionBtn.onclick = stopEarly; // Allow manual stop?
}

function stopEarly() {
    if (isRecording) {
        clearInterval(timerInterval);
        mediaRecorder.stop();
        isRecording = false;
        // nextQuestion called by mediaRecorder.onstop -> upload -> nextQuestion
    }
}

function startTimer(onComplete) {
    let current = timeLeft;
    const initial = timeLeft;
    updateTimerUI(current, initial);

    timerInterval = setInterval(() => {
        current--;
        updateTimerUI(current, initial);

        // Color change logic (Green -> Red)
        if (current <= 10) {
            timerRing.classList.remove('text-neon');
            timerRing.classList.add('text-red-500');
        } else {
            timerRing.classList.add('text-neon');
            timerRing.classList.remove('text-red-500');
        }

        if (current <= 0) {
            clearInterval(timerInterval);
            if (isRecording) {
                mediaRecorder.stop();
                isRecording = false;
            } else {
                onComplete();
            }
        }
    }, 1000);
}

function updateTimerUI(current, initial) {
    timerText.innerText = current;
    const percent = (current / initial) * 100;
    setProgress(percent);
}

function nextQuestion() {
    currentQuestionIndex++;
    loadQuestion(currentQuestionIndex);
}

// Start
init();
