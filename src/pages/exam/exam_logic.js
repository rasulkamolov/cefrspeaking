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
const timerLabel = document.getElementById('timer-label'); // Added ID in runner.php

// Part 3 Elements
const c1Container = document.getElementById('c1-container');
const c1Topic = document.getElementById('c1-topic');
const c1ForList = document.getElementById('c1-for-list');
const c1AgainstList = document.getElementById('c1-against-list');

// Circle Params
const radius = 40;
const circumference = 2 * Math.PI * radius;
timerRing.style.strokeDasharray = `${circumference} ${circumference}`;

function setProgress(percent) {
    const offset = circumference - (percent / 100) * circumference;
    timerRing.style.strokeDashoffset = offset;
}

function init() {
    // Check permission immediately but don't alert aggressively
    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            setupRecorder(stream);
            startQuestionSequence();
        })
        .catch(err => {
            console.warn("Microphone access denied or error:", err);
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
    const formData = new FormData();
    formData.append('audio', blob, 'recording.webm');
    formData.append('submission_id', submissionId);
    formData.append('question_id', questions[currentQuestionIndex].id);

    try {
        await fetch('../../api/upload_audio.php', {
            method: 'POST',
            body: formData
        });
        nextQuestion();
    } catch (e) {
        console.error("Upload failed", e);
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
    let image2 = q.media_url_2; // Added secondary image

    // Reset UI
    questionArea.innerHTML = '';
    c1Container.classList.add('hidden');

    // Parse JSON content if needed
    try {
        const parsed = JSON.parse(content);
        if (typeof parsed === 'object') content = parsed;
    } catch(e) {}

    // Determine UI based on Part
    if (currentPart === '1.1') {
        // Simple Text
        questionArea.innerHTML = `<h2 class="text-2xl md:text-3xl font-bold mb-4 text-textMain">${typeof content === 'string' ? content : content[0]}</h2>`;
        timeLeft = 30;
        isPrepTime = false;
        startPhase(nextQuestion);
    }
    else if (currentPart === '1.2') {
        // Two Images + Text
        const prompt = Array.isArray(content) ? content[0] : content;

        // Helper to handle external vs local images
        const getImgSrc = (url) => url.startsWith('http') ? url : `../../../${url}`;

        let imagesHtml = '';
        if (image && image2) {
            // Two images vertically stacked - Optimized for mobile (max-h reduced)
            imagesHtml = `
                <div class="flex flex-col space-y-2 mb-4">
                    <img src="${getImgSrc(image)}" class="w-full max-h-[25vh] md:max-h-[35vh] object-contain rounded-lg shadow-md bg-gray-50 p-1">
                    <img src="${getImgSrc(image2)}" class="w-full max-h-[25vh] md:max-h-[35vh] object-contain rounded-lg shadow-md bg-gray-50 p-1">
                </div>
            `;
        } else if (image) {
             // Fallback single image
             imagesHtml = `<img src="${getImgSrc(image)}" class="w-full max-h-[40vh] mx-auto rounded-lg shadow-md mb-4 object-contain bg-gray-50 p-2">`;
        }

        questionArea.innerHTML = `
            ${imagesHtml}
            <h2 class="text-xl md:text-2xl font-bold mb-4 text-textMain">${prompt}</h2>
        `;
        timeLeft = 30; // 30s per question (or pair)
        isPrepTime = false;
        startPhase(nextQuestion);
    }
    else if (currentPart === '2') {
        // Part 2: Prep + Monologue - NO NOTEPAD
        if (!isPrepTime) {
            // Start Prep Phase
            isPrepTime = true;
            timeLeft = 60; // 1 min prep

            // Topic + Optional Image
            let topicText = typeof content === 'string' ? content : (content.topic || content);
            let prepHtml = `<h2 class="text-3xl font-bold mb-4 text-textMain">Topic: ${topicText}</h2>`;

            if (content.points && Array.isArray(content.points)) {
                prepHtml += `<ul class="text-left text-lg list-disc list-inside bg-gray-50 p-4 rounded-lg mb-4 space-y-2">`;
                content.points.forEach(p => {
                    prepHtml += `<li>${p}</li>`;
                });
                prepHtml += `</ul>`;
            }

            if (image) {
                const imgSrc = image.startsWith('http') ? image : `../../../${image}`;
                prepHtml += `<img src="${imgSrc}" class="h-48 mx-auto rounded-lg shadow-md mb-4 object-contain bg-gray-50 p-2">`;
            }
            questionArea.innerHTML = prepHtml;

            // Visual indicator for Prep
            actionBtn.classList.add('opacity-50', 'cursor-not-allowed', 'ring-yellow-100');
            actionBtn.classList.remove('bg-red-500', 'hover:ring-red-100');
            actionBtn.classList.add('bg-yellow-500');
            recIcon.className = "w-8 h-8 bg-white rounded-full opacity-50";

            // Start Prep Countdown, then Prep Timer
            startPhase(() => {
                // End Prep, Start Recording
                isPrepTime = false;
                timeLeft = 120; // 2 min

                // Reset Button Style
                actionBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'ring-yellow-100', 'bg-yellow-500');
                actionBtn.classList.add('bg-red-500', 'hover:ring-red-100');
                recIcon.className = "w-8 h-8 bg-white rounded-sm";

                // Start Recording Countdown, then Recording Timer
                startPhase(nextQuestion);
            });
            return;
        }
    }
    else if (currentPart === '3') {
        // C1: Debate - Now with Prep Phase
        if (!isPrepTime) {
            // Start Prep Phase
            isPrepTime = true;
            timeLeft = 60; // 1 min prep

            const topic = content.topic || "Debate Topic";
            const fors = content.for_prompts || [];
            const againsts = content.against_prompts || [];

            c1Topic.innerText = topic;
            c1ForList.innerHTML = fors.map(p => `<li class="p-2 bg-green-50 rounded text-green-900 font-medium text-sm">• ${p}</li>`).join('');
            c1AgainstList.innerHTML = againsts.map(p => `<li class="p-2 bg-red-50 rounded text-red-900 font-medium text-sm">• ${p}</li>`).join('');

            c1Container.classList.remove('hidden');
            questionArea.appendChild(c1Container);

            // Visual indicator for Prep
            actionBtn.classList.add('opacity-50', 'cursor-not-allowed', 'ring-yellow-100');
            actionBtn.classList.remove('bg-red-500', 'hover:ring-red-100');
            actionBtn.classList.add('bg-yellow-500');
            recIcon.className = "w-8 h-8 bg-white rounded-full opacity-50";

            // Start Prep Countdown, then Prep Timer
            startPhase(() => {
                // End Prep, Start Recording
                isPrepTime = false;
                timeLeft = 120; // 2 min (Debate)

                // Reset Button Style
                actionBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'ring-yellow-100', 'bg-yellow-500');
                actionBtn.classList.add('bg-red-500', 'hover:ring-red-100');
                recIcon.className = "w-8 h-8 bg-white rounded-sm";

                // Start Recording Countdown, then Recording Timer
                startPhase(nextQuestion);
            });
            return;
        }
    }
    else {
        // Fallback for unknown parts?
        startPhase(nextQuestion);
    }
}

// Renamed and upgraded from startRecordingPhase
function startPhase(onTimerComplete) {
    overlay.classList.remove('hidden');
    let count = 3;
    countdownEl.innerText = count;

    // UI Updates
    overlayText.innerText = isPrepTime ? "Preparation Starts In..." : "Recording Starts In...";

    if (timerLabel) {
        timerLabel.innerHTML = isPrepTime ? "Preparation<br>Time" : "Recording<br>Time";
    }

    const countInt = setInterval(() => {
        count--;
        if (count > 0) {
            countdownEl.innerText = count;
        } else {
            clearInterval(countInt);
            overlay.classList.add('hidden');

            startTimer(onTimerComplete);

            if (!isPrepTime) {
                startRecording();
            }
        }
    }, 1000);
}

function startRecording() {
    isRecording = true;
    audioChunks = [];
    if (mediaRecorder && mediaRecorder.state === 'inactive') {
         mediaRecorder.start();
         recIcon.className = "w-8 h-8 bg-white rounded-sm animate-pulse";
         actionBtn.onclick = stopEarly;
    } else {
        console.error("Recorder not ready or already recording");
    }
}

function stopEarly() {
    if (isRecording) {
        clearInterval(timerInterval);
        mediaRecorder.stop();
        isRecording = false;
    }
}

function startTimer(onComplete) {
    // Clear any existing timer to prevent overlaps
    if (timerInterval) clearInterval(timerInterval);

    let current = timeLeft;
    const initial = timeLeft;
    updateTimerUI(current, initial);

    timerInterval = setInterval(() => {
        current--;
        updateTimerUI(current, initial);

        if (current <= 10) {
            timerRing.classList.remove('text-primary');
            timerRing.classList.add('text-danger');
            timerText.classList.add('text-danger');
        } else {
            timerRing.classList.add('text-primary');
            timerRing.classList.remove('text-danger');
            timerText.classList.remove('text-danger');
        }

        if (current <= 0) {
            clearInterval(timerInterval);
            if (isRecording) {
                mediaRecorder.stop();
                isRecording = false;
                // onstop handler calls nextQuestion, so we don't call onComplete here
            } else {
                if (onComplete) onComplete();
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

init();
