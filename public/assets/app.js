/**
 * Project: PPL Study
 * File: public/assets/app.js
 * Module Type: Public Asset
 *
 * Purpose:
 *   Provides local study progress, multiple-choice scoring, navigation, and the
 *   browser-driven drill state machine.
 *
 * Security and privacy:
 *   - Answer selections are evaluated in the browser and are never sent to the server.
 *   - Study progress is stored only in localStorage in the current browser.
 *   - Question-bank strings are rendered with textContent, never as trusted HTML.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 */

(() => {
    'use strict';

    const PROGRESS_KEY = 'pplStudyProgressV1';

    function readProgress() {
        try {
            const parsed = JSON.parse(localStorage.getItem(PROGRESS_KEY) || '{}');
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (_) {
            return {};
        }
    }

    function writeProgress(progress) {
        try {
            localStorage.setItem(PROGRESS_KEY, JSON.stringify(progress));
        } catch (_) {
            // Browsers can disable or quota-limit localStorage. Studying still works.
        }
    }

    function saveAssessment(sequence, subjectId, result) {
        if (!sequence) return;
        const progress = readProgress();
        const previous = progress[String(sequence)] || {};
        progress[String(sequence)] = {
            ...previous,
            subjectId: Number(subjectId) || Number(previous.subjectId) || 0,
            result,
            assessedAt: new Date().toISOString()
        };
        writeProgress(progress);
    }

    function toggleStar(sequence, subjectId) {
        const progress = readProgress();
        const key = String(sequence);
        const previous = progress[key] || {};
        progress[key] = {
            ...previous,
            subjectId: Number(subjectId) || Number(previous.subjectId) || 0,
            starred: !previous.starred
        };
        writeProgress(progress);
        return Boolean(progress[key].starred);
    }

    function setStarButton(button, active) {
        if (!button) return;
        button.textContent = active ? '★ Označeno' : '☆ Označit';
        button.setAttribute('aria-pressed', active ? 'true' : 'false');
    }

    function initializeNavigation() {
        const toggle = document.querySelector('[data-nav-toggle]');
        const nav = document.querySelector('[data-nav]');
        if (!toggle || !nav) return;
        toggle.addEventListener('click', () => nav.classList.toggle('open'));
    }

    function initializeHomeProgress() {
        const cards = document.querySelectorAll('[data-subject-card]');
        if (!cards.length) return;
        const progress = readProgress();
        const values = Object.values(progress);
        cards.forEach((card) => {
            const subjectId = Number(card.dataset.subjectId);
            const total = Number(card.dataset.questionCount) || 0;
            const attempted = values.filter((entry) => Number(entry.subjectId) === subjectId && (entry.result === 'correct' || entry.result === 'wrong')).length;
            const correct = values.filter((entry) => Number(entry.subjectId) === subjectId && entry.result === 'correct').length;
            const percent = total > 0 ? Math.min(100, Math.round((attempted / total) * 100)) : 0;
            const bar = card.querySelector('[data-progress-bar]');
            const label = card.querySelector('[data-progress-label]');
            if (bar) bar.style.width = `${percent}%`;
            if (label) label.textContent = attempted ? `${attempted} / ${total} viděno, ${correct} naposledy správně` : 'Progress začne první vyhodnocenou kartou.';
        });
    }

    function initializeQuestionPage() {
        const root = document.querySelector('[data-question-page]');
        if (!root) return;
        const sequence = Number(root.dataset.sequence);
        const subjectId = Number(root.dataset.subjectId);
        const panel = root.querySelector('[data-answer-panel]');
        const feedback = root.querySelector('[data-choice-feedback]');
        const star = root.querySelector('[data-star]');
        const choices = Array.from(root.querySelectorAll('[data-choice]'));
        const progress = readProgress();
        let answered = false;
        setStarButton(star, Boolean(progress[String(sequence)]?.starred));

        choices.forEach((button) => {
            button.addEventListener('click', () => {
                if (answered) return;
                answered = true;
                const isCorrect = button.dataset.correct === '1';
                saveAssessment(sequence, subjectId, isCorrect ? 'correct' : 'wrong');

                choices.forEach((candidate) => {
                    candidate.disabled = true;
                    if (candidate.dataset.correct === '1') candidate.classList.add('correct');
                });
                button.classList.add(isCorrect ? 'selected-correct' : 'selected-wrong');

                if (feedback) {
                    feedback.hidden = false;
                    feedback.className = `choice-feedback ${isCorrect ? 'correct' : 'wrong'}`;
                    feedback.textContent = isCorrect ? 'Správně.' : 'Správná odpověď je zvýrazněná.';
                }
                if (panel) panel.hidden = false;
            });
        });

        star?.addEventListener('click', () => setStarButton(star, toggleStar(sequence, subjectId)));
    }

    function appendText(parent, tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        node.textContent = text || '';
        parent.appendChild(node);
        return node;
    }

    function appendButton(parent, className, text, handler) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.textContent = text;
        button.addEventListener('click', handler);
        parent.appendChild(button);
        return button;
    }

    function initializeDrill() {
        const root = document.querySelector('[data-drill-root]');
        if (!root) return;

        const form = root.querySelector('[data-drill-form]');
        const subject = root.querySelector('[data-drill-subject]');
        const mode = root.querySelector('[data-drill-mode]');
        const countField = root.querySelector('[data-count-field]');
        const countInput = form?.querySelector('input[name="count"]');
        const paperHint = root.querySelector('[data-paper-hint]');
        const stage = root.querySelector('[data-drill-stage]');
        const card = root.querySelector('[data-drill-card]');
        const result = root.querySelector('[data-drill-result]');
        const position = root.querySelector('[data-drill-position]');
        const title = root.querySelector('[data-drill-title]');
        const progressBar = root.querySelector('[data-drill-progress]');
        const timerNode = root.querySelector('[data-drill-timer]');
        const apiUrl = root.dataset.apiUrl;

        let drill = null;
        let index = 0;
        let correct = 0;
        let wrong = 0;
        let answers = [];
        let timerHandle = null;
        let remainingSeconds = 0;
        let finished = false;

        function stopTimer() {
            if (timerHandle !== null) window.clearInterval(timerHandle);
            timerHandle = null;
        }

        function updateModeFields() {
            const paper = mode?.value === 'paper';
            if (countField) countField.hidden = paper;
            if (paperHint) {
                const option = subject?.selectedOptions?.[0];
                if (paper && subject?.value !== 'all') {
                    paperHint.textContent = `Paper: ${option?.dataset.paperCount || '?'} otázek, ${option?.dataset.paperTime || '?'} min, hranice 75 %.`;
                } else if (paper) {
                    paperHint.textContent = 'Paper drill vyžaduje jeden konkrétní předmět.';
                } else {
                    paperHint.textContent = 'Vlastní drill může míchat všechny předměty nebo zůstat v jednom předmětu.';
                }
            }
        }

        function renderTimer() {
            if (!timerNode) return;
            const minutes = Math.floor(Math.max(remainingSeconds, 0) / 60);
            const seconds = Math.max(remainingSeconds, 0) % 60;
            timerNode.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            timerNode.classList.toggle('urgent', remainingSeconds <= 60);
        }

        function startTimer(minutes) {
            stopTimer();
            remainingSeconds = Number(minutes) * 60;
            if (!timerNode || remainingSeconds <= 0) {
                if (timerNode) timerNode.hidden = true;
                return;
            }
            timerNode.hidden = false;
            renderTimer();
            timerHandle = window.setInterval(() => {
                remainingSeconds -= 1;
                renderTimer();
                if (remainingSeconds <= 0) finishDrill(true);
            }, 1000);
        }

        function finishDrill(timedOut = false) {
            if (!drill || finished) return;
            finished = true;
            stopTimer();
            if (stage) stage.hidden = true;
            if (!result) return;
            result.hidden = false;
            result.replaceChildren();

            const total = drill.questions.length;
            const assessed = correct + wrong;
            const unanswered = Math.max(0, total - assessed);
            const percent = total > 0 ? Math.round((correct / total) * 100) : 0;
            appendText(result, 'span', 'eyebrow', timedOut ? 'Čas vypršel' : 'Drill dokončen');
            appendText(result, 'h2', '', drill.title || 'Výsledek');
            const metrics = document.createElement('div');
            metrics.className = 'result-metrics';
            [[correct, 'správně'], [wrong, 'chybně'], [unanswered, 'bez odpovědi'], [`${percent} %`, 'skóre']].forEach(([value, label]) => {
                const box = document.createElement('div');
                appendText(box, 'strong', '', String(value));
                appendText(box, 'span', '', String(label));
                metrics.appendChild(box);
            });
            result.appendChild(metrics);
            const verdict = appendText(result, 'p', percent >= Number(drill.pass_percent || 75) ? 'result-pass' : 'result-fail', percent >= Number(drill.pass_percent || 75) ? 'Hranice 75 % dosažena.' : 'Výsledek je pod hranicí 75 %.');
            if (drill.mode !== 'paper') verdict.textContent += ' Vlastní drill slouží k průběžnému procvičování.';

            const incorrectAnswers = answers.filter((entry) => !entry.correct);
            if (incorrectAnswers.length) {
                const review = document.createElement('div');
                review.className = 'result-review';
                appendText(review, 'h3', '', 'Otázky k opakování');
                incorrectAnswers.forEach((entry) => {
                    const item = document.createElement('details');
                    item.className = 'review-item';
                    const summary = document.createElement('summary');
                    summary.textContent = `#${String(entry.question.sequence_no).padStart(3, '0')} ${entry.question.prompt}`;
                    item.appendChild(summary);
                    appendText(item, 'p', 'review-selected', `Tvoje odpověď: ${entry.selected}`);
                    appendText(item, 'p', 'review-correct', `Správná odpověď: ${entry.question.answer}`);
                    appendText(item, 'p', 'muted', entry.question.explanation || '');
                    review.appendChild(item);
                });
                result.appendChild(review);
            }

            appendButton(result, 'button primary', 'Spustit nový drill', () => {
                result.hidden = true;
                form?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }

        function renderQuestion() {
            if (!drill || !card || index >= drill.questions.length) {
                finishDrill(false);
                return;
            }
            card.replaceChildren();
            const q = drill.questions[index];
            if (position) position.textContent = `${index + 1} / ${drill.questions.length}`;
            if (progressBar) progressBar.style.width = `${Math.round((index / drill.questions.length) * 100)}%`;

            const meta = document.createElement('div');
            meta.className = 'drill-question-meta';
            appendText(meta, 'span', '', q.subject_name || '');
            appendText(meta, 'span', '', `#${String(q.sequence_no).padStart(3, '0')}`);
            card.appendChild(meta);
            appendText(card, 'div', 'study-label', 'Kontrolní otázka');
            appendText(card, 'p', 'question-prompt', q.prompt);

            if (q.attachment_code) {
                const attachment = document.createElement('div');
                attachment.className = 'attachment-prompt';
                appendText(attachment, 'strong', '', `Příloha ${q.attachment_code}`);
                if (q.attachment_url) {
                    const link = document.createElement('a');
                    link.href = q.attachment_url;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.className = 'text-link';
                    link.textContent = 'Otevřít podklad k otázce';
                    attachment.appendChild(link);
                }
                card.appendChild(attachment);
            }

            if (!Array.isArray(q.choices) || q.choices.length !== 4) {
                appendText(card, 'div', 'flash error', 'Tato karta zatím nemá kompletní sadu čtyř odpovědí.');
                return;
            }

            const choiceList = document.createElement('div');
            choiceList.className = 'choice-list';
            const choiceButtons = [];
            q.choices.forEach((choice, choiceIndex) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'choice-button';
                const letter = document.createElement('span');
                letter.className = 'choice-letter';
                letter.textContent = String.fromCharCode(65 + choiceIndex);
                const text = document.createElement('span');
                text.className = 'choice-text';
                text.textContent = choice;
                button.append(letter, text);
                button.addEventListener('click', () => {
                    if (finished || button.disabled) return;
                    const isCorrect = choice === q.answer;
                    if (isCorrect) correct += 1; else wrong += 1;
                    answers.push({ question: q, selected: choice, correct: isCorrect });
                    saveAssessment(q.sequence_no, q.subject_id, isCorrect ? 'correct' : 'wrong');
                    choiceButtons.forEach((candidate) => candidate.disabled = true);

                    if (drill.mode === 'paper') {
                        button.classList.add('selected');
                        const actions = document.createElement('div');
                        actions.className = 'question-actions';
                        appendButton(actions, 'button primary', index + 1 >= drill.questions.length ? 'Vyhodnotit paper' : 'Další otázka', () => {
                            index += 1;
                            if (index >= drill.questions.length) finishDrill(false); else renderQuestion();
                        });
                        card.appendChild(actions);
                        return;
                    }

                    choiceButtons.forEach((candidate) => {
                        if (candidate.dataset.answer === q.answer) candidate.classList.add('correct');
                    });
                    button.classList.add(isCorrect ? 'selected-correct' : 'selected-wrong');
                    const feedback = appendText(card, 'div', `choice-feedback ${isCorrect ? 'correct' : 'wrong'}`, isCorrect ? 'Správně.' : 'Správná odpověď je zvýrazněná.');
                    feedback.hidden = false;

                    const answerPanel = document.createElement('div');
                    answerPanel.className = 'answer-panel';
                    appendText(answerPanel, 'div', 'study-label success', 'Správná odpověď');
                    appendText(answerPanel, 'p', 'correct-answer', q.answer);
                    const explanation = document.createElement('div');
                    explanation.className = 'explanation-block';
                    appendText(explanation, 'h2', '', 'Proč');
                    appendText(explanation, 'p', '', q.explanation);
                    answerPanel.appendChild(explanation);
                    if (q.warning) {
                        const warning = document.createElement('div');
                        warning.className = 'warning-block';
                        appendText(warning, 'strong', '', 'Pozor');
                        appendText(warning, 'p', '', q.warning);
                        answerPanel.appendChild(warning);
                    }
                    const actions = document.createElement('div');
                    actions.className = 'question-actions';
                    const local = readProgress();
                    const starButton = appendButton(actions, 'button ghost', '', () => setStarButton(starButton, toggleStar(q.sequence_no, q.subject_id)));
                    setStarButton(starButton, Boolean(local[String(q.sequence_no)]?.starred));
                    appendButton(actions, 'button primary', index + 1 >= drill.questions.length ? 'Dokončit drill' : 'Další otázka', () => {
                        index += 1;
                        if (index >= drill.questions.length) finishDrill(false); else renderQuestion();
                    });
                    answerPanel.appendChild(actions);
                    card.appendChild(answerPanel);
                });
                button.dataset.answer = choice;
                choiceButtons.push(button);
                choiceList.appendChild(button);
            });
            card.appendChild(choiceList);
        }

        async function startDrill() {
            if (!form || !subject || !mode || !stage || !card || !result) return;
            if (mode.value === 'paper' && subject.value === 'all') {
                paperHint.textContent = 'Vyber konkrétní předmět pro paper drill.';
                subject.focus();
                return;
            }
            finished = false;
            stopTimer();
            correct = 0;
            wrong = 0;
            answers = [];
            index = 0;
            result.hidden = true;
            stage.hidden = false;
            card.replaceChildren();
            appendText(card, 'div', 'drill-loading', 'Načítám náhodný výběr...');

            const params = new URLSearchParams({ mode: mode.value, subject: subject.value });
            if (mode.value !== 'paper') params.set('count', String(Number(countInput?.value) || 20));
            try {
                const response = await fetch(`${apiUrl}?${params.toString()}`, { headers: { Accept: 'application/json' } });
                const payload = await response.json();
                if (!response.ok || !payload.ok || !payload.drill) throw new Error(payload.error || `HTTP ${response.status}`);
                drill = payload.drill;
                if (title) title.textContent = drill.title || 'Drill';
                if (drill.mode === 'paper') startTimer(drill.time_minutes); else if (timerNode) timerNode.hidden = true;
                renderQuestion();
            } catch (error) {
                stopTimer();
                card.replaceChildren();
                appendText(card, 'div', 'flash error', `Drill se nepodařilo načíst: ${error.message}`);
            }
        }

        mode?.addEventListener('change', updateModeFields);
        subject?.addEventListener('change', updateModeFields);
        form?.addEventListener('submit', (event) => {
            event.preventDefault();
            startDrill();
        });
        updateModeFields();

        const params = new URLSearchParams(window.location.search);
        if (params.get('mode') === 'paper' && params.get('subject')) startDrill();
    }

    initializeNavigation();
    initializeHomeProgress();
    initializeQuestionPage();
    initializeDrill();
})();
