/**
 * Maratech AI Accessibility Assistant
 * Features: Speech-to-Text, Text-to-Speech, Navigation Commands
 */

class MaratechChatbot {
    constructor() {
        this.widget = document.getElementById('chatbot-widget');
        this.messagesContainer = document.getElementById('chatbot-messages');
        this.inputField = document.getElementById('chatbot-input-field');
        this.sendBtn = document.getElementById('chatbot-send-btn');
        this.voiceBtn = document.getElementById('chatbot-voice-btn');
        this.isListening = false;

        // Web Speech API
        this.recognition = null;
        this.synthesis = window.speechSynthesis;

        this.initRecognition();
        this.initEventListeners();
    }

    initRecognition() {
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.recognition = new SpeechRecognition();
            this.recognition.lang = 'fr-FR';
            this.recognition.interimResults = false;
            this.recognition.continuous = false;

            this.recognition.onstart = () => {
                this.isListening = true;
                this.voiceBtn.classList.add('listening');
                this.updateAIStatus('Je vous écoute...');
            };

            this.recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                this.handleUserInput(transcript);
            };

            this.recognition.onerror = (event) => {
                console.error('Speech recognition error:', event.error);
                this.stopListening();
            };

            this.recognition.onend = () => {
                this.stopListening();
            };
        } else {
            this.voiceBtn.style.display = 'none';
            console.warn('Speech Recognition not supported in this browser.');
        }
    }

    initEventListeners() {
        this.sendBtn.addEventListener('click', () => this.handleUserInput(this.inputField.value));
        this.inputField.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.handleUserInput(this.inputField.value);
        });
        this.voiceBtn.addEventListener('click', () => this.toggleVoice());
    }

    toggleVoice() {
        if (this.isListening) {
            this.recognition.stop();
        } else {
            this.recognition.start();
        }
    }

    stopListening() {
        this.isListening = false;
        this.voiceBtn.classList.remove('listening');
    }

    updateAIStatus(text) {
        const title = this.widget.querySelector('.chatbot-title p');
        if (title) title.innerText = text;
    }

    handleUserInput(text) {
        if (!text.trim()) return;

        this.addMessage(text, 'user');
        this.inputField.value = '';

        // Processing commands
        const response = this.processCommand(text.toLowerCase());
        setTimeout(() => {
            this.addMessage(response, 'ai');
            this.speak(response);
        }, 600);
    }

    processCommand(text) {
        // Navigation Commands
        if (text.includes('don') || text.includes('aider')) {
            window.location.hash = '#cases';
            return "Je vous dirige vers la section des dons.";
        }
        if (text.includes('contact') || text.includes('message')) {
            window.location.hash = '#contact';
            return "Voici nos informations de contact.";
        }
        if (text.includes('défile') || text.includes('bas')) {
            window.scrollBy({ top: 500, behavior: 'smooth' });
            return "Je fais défiler la page pour vous.";
        }
        if (text.includes('haut')) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return "Retour en haut de la page.";
        }
        if (text.includes('qui') || text.includes('maratech')) {
            return "Maratech est une plateforme solidaire dédiée à l'entraide et au soutien des associations en Tunisie.";
        }
        if (text.includes('connexion') || text.includes('login')) {
            document.getElementById('loginModal').style.display = 'flex';
            return "J'ouvre la fenêtre de connexion.";
        }
        if (text.includes('bonjour') || text.includes('salut')) {
            return "Bonjour ! Je suis là pour vous aider à naviguer. Vous pouvez me demander d'aller aux dons ou de défiler la page.";
        }

        return "Je n'ai pas bien compris. Pouvez-vous reformuler ? Vous pouvez dire 'Aller aux dons' ou 'Aide-moi'.";
    }

    addMessage(text, sender) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `message ${sender}-message`;
        msgDiv.innerText = text;
        this.messagesContainer.appendChild(msgDiv);
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    }

    speak(text) {
        if (this.synthesis.speaking) this.synthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'fr-FR';
        utterance.rate = 1;
        this.synthesis.speak(utterance);
    }
}

// Global Chatbot Instance
let maratechAI;
document.addEventListener('DOMContentLoaded', () => {
    maratechAI = new MaratechChatbot();
});

function toggleChatbot() {
    const widget = document.getElementById('chatbot-widget');
    widget.classList.toggle('active');
}
