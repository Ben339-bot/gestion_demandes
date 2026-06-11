// ===== SIDEBAR MOBILE =====
const burger = document.getElementById('burger');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

if (burger) {
    burger.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    });
}

if (overlay) {
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
    });
}

// ===== INFO DYNAMIQUE =====
const select = document.getElementById('type_demande');
const infoBox = document.getElementById('info-box');
const infoText = document.getElementById('info-text');

const infos = {
    'réclamation': '🗒️ Précisez la matière, le semestre et la note obtenue. Joignez votre relevé de notes si possible.',
    'dérogation': '📌 Expliquez clairement le motif et les circonstances exceptionnelles de votre demande.',
    'duplicata': '📄 Indiquez le type de document souhaité (attestation de scolarité, copie de diplôme...) et son usage prévu.'
};

if (select) {
    select.addEventListener('change', function () {
        if (infos[this.value]) {
            infoText.textContent = infos[this.value];
            infoBox.style.display = 'block';
        } else {
            infoBox.style.display = 'none';
        }
    });
}

// ===== UPLOAD FICHIER =====
const fileInput = document.getElementById('fichier');
const uploadArea = document.getElementById('upload-area');
const uploadContent = document.getElementById('upload-content');
const uploadPreview = document.getElementById('upload-preview');
const fileName = document.getElementById('file-name');
const fileRemove = document.getElementById('file-remove');

if (fileInput) {
    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            showPreview(this.files[0]);
        }
    });
}

if (uploadArea) {
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file) {
            fileInput.files = e.dataTransfer.files;
            showPreview(file);
        }
    });
}

function showPreview(file) {
    fileName.textContent = file.name;
    uploadContent.style.display = 'none';
    uploadPreview.style.display = 'flex';
}

if (fileRemove) {
    fileRemove.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.value = '';
        uploadContent.style.display = 'block';
        uploadPreview.style.display = 'none';
    });
}

// ===== TOGGLE DETAIL DEMANDE =====
function toggleDetail(id) {
    const detail = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);

    if (detail) {
        detail.classList.toggle('open');
    }
    if (icon) {
        icon.classList.toggle('open');
    }
}