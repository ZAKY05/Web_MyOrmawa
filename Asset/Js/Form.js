let currentFieldIndex = 0;
let formFields = [];
let editingFieldIndex = -1;

function addField(type) {
    editingFieldIndex = -1;
    document.getElementById('fieldConfigForm').reset();
    document.getElementById('fieldName').value = `field_${currentFieldIndex + 1}`;
    document.getElementById('fieldLabel').value = getDefaultLabel(type);
    
    // Show/hide options based on field type
    if (type === 'radio' || type === 'select') {
        document.getElementById('optionsContainer').style.display = 'block';
        document.getElementById('fieldOptions').value = 'Opsi 1, Opsi 2, Opsi 3';
    } else {
        document.getElementById('optionsContainer').style.display = 'none';
    }
    
    // Store the field type temporarily
    window.tempFieldType = type;
    
    // Show modal
    new bootstrap.Modal(document.getElementById('fieldConfigModal')).show();
}

function getDefaultLabel(type) {
    const labels = {
        'text': 'Input Text',
        'number': 'Input Angka',
        'file': 'Upload File',
        'textarea': 'Textarea',
        'radio': 'Radio Button',
        'select': 'Select Option'
    };
    return labels[type] || 'Field';
}

function saveFieldConfig() {
    const fieldName = document.getElementById('fieldName').value;
    const fieldLabel = document.getElementById('fieldLabel').value;
    const fieldOptions = document.getElementById('fieldOptions').value;
    const fieldRequired = document.getElementById('fieldRequired').checked;
    const fieldType = window.tempFieldType;

    if (!fieldName || !fieldLabel) {
        alert('Nama field dan label harus diisi!');
        return;
    }

    const fieldData = {
        id: editingFieldIndex >= 0 ? formFields[editingFieldIndex].id : currentFieldIndex++,
        name: fieldName,
        label: fieldLabel,
        type: fieldType,
        options: fieldOptions ? fieldOptions.split(',').map(opt => opt.trim()) : [],
        required: fieldRequired
    };

    if (editingFieldIndex >= 0) {
        formFields[editingFieldIndex] = fieldData;
    } else {
        formFields.push(fieldData);
    }

    renderForm();
    updatePreview();
    updateFieldCount();
    
    bootstrap.Modal.getInstance(document.getElementById('fieldConfigModal')).hide();
}

function editField(index) {
    editingFieldIndex = index;
    const field = formFields[index];
    
    document.getElementById('fieldName').value = field.name;
    document.getElementById('fieldLabel').value = field.label;
    document.getElementById('fieldRequired').checked = field.required;
    
    if (field.type === 'radio' || field.type === 'select') {
        document.getElementById('optionsContainer').style.display = 'block';
        document.getElementById('fieldOptions').value = field.options.join(', ');
    } else {
        document.getElementById('optionsContainer').style.display = 'none';
    }
    
    window.tempFieldType = field.type;
    
    new bootstrap.Modal(document.getElementById('fieldConfigModal')).show();
}

function deleteField(index) {
    if (confirm('Yakin ingin menghapus field ini?')) {
        formFields.splice(index, 1);
        renderForm();
        updatePreview();
        updateFieldCount();
    }
}

function renderForm() {
    const canvas = document.getElementById('formCanvas');
    
    if (formFields.length === 0) {
        canvas.innerHTML = `
            <div class="canvas-placeholder">
                <i class="fas fa-mouse-pointer"></i>
                <h6>Mulai Buat Form</h6>
                <p>Klik pada elemen di sebelah kiri untuk menambahkan field ke form</p>
            </div>
        `;
        return;
    }

    let html = '';
    formFields.forEach((field, index) => {
        html += `
            <div class="field-item">
                <div class="field-controls">
                    <button class="field-control-btn btn-edit" onclick="editField(${index})" title="Edit Field">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="field-control-btn btn-delete" onclick="deleteField(${index})" title="Hapus Field">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                
                <label class="field-label">
                    ${field.label} ${field.required ? '<span class="text-required">*</span>' : ''}
                </label>
                ${renderFieldInput(field)}
                <div class="field-meta">
                    <i class="fas fa-tag me-1"></i>${field.name} | 
                    <i class="fas fa-cube me-1"></i>${field.type}
                </div>
            </div>
        `;
    });

    canvas.innerHTML = html;
}

function renderFieldInput(field) {
    switch (field.type) {
        case 'text':
            return `<input type="text" class="form-control" name="${field.name}" placeholder="Masukkan ${field.label.toLowerCase()}" ${field.required ? 'required' : ''}>`;
        
        case 'number':
            return `<input type="number" class="form-control" name="${field.name}" placeholder="Masukkan angka" ${field.required ? 'required' : ''}>`;
        
        case 'file':
            return `<input type="file" class="form-control" name="${field.name}" ${field.required ? 'required' : ''}>`;
        
        case 'textarea':
            return `<textarea class="form-control" name="${field.name}" rows="3" placeholder="Masukkan ${field.label.toLowerCase()}" ${field.required ? 'required' : ''}></textarea>`;
        
        case 'radio':
            let radioHtml = '<div class="mt-2">';
            field.options.forEach((option, i) => {
                radioHtml += `
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="${field.name}" id="${field.name}_${i}" value="${option}" ${field.required ? 'required' : ''}>
                        <label class="form-check-label" for="${field.name}_${i}">${option}</label>
                    </div>
                `;
            });
            radioHtml += '</div>';
            return radioHtml;
        
        case 'select':
            let selectHtml = `<select class="form-select" name="${field.name}" ${field.required ? 'required' : ''}>
                <option value="">-- Pilih ${field.label} --</option>`;
            field.options.forEach(option => {
                selectHtml += `<option value="${option}">${option}</option>`;
            });
            selectHtml += '</select>';
            return selectHtml;
        
        default:
            return `<input type="text" class="form-control" name="${field.name}">`;
    }
}

function updatePreview() {
    const preview = document.getElementById('formPreview');
    const formName = document.getElementById('formName').value || 'Form Recruitment';
    const formDescription = document.getElementById('formDescription').value || 'Deskripsi form recruitment';

    if (formFields.length === 0 && !document.getElementById('formName').value) {
        preview.innerHTML = `
            <div class="preview-placeholder">
                <i class="fas fa-eye"></i>
                <p>Preview form akan muncul di sini</p>
            </div>
        `;
        return;
    }

    let html = `
        <div class="preview-form">
            <h6>${formName}</h6>
            <div class="form-description">${formDescription}</div>
    `;

    formFields.forEach(field => {
        html += `
            <div class="preview-field">
                <label>
                    ${field.label} ${field.required ? '<span class="text-required">*</span>' : ''}
                </label>
                ${renderPreviewField(field)}
            </div>
        `;
    });

    if (formFields.length > 0) {
        html += `
                <button type="submit" class="preview-submit">
                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                </button>
        `;
    }

    html += '</div>';
    preview.innerHTML = html;
}

function renderPreviewField(field) {
    switch (field.type) {
        case 'text':
            return `<input type="text" class="form-control form-control-sm" disabled>`;
        
        case 'number':
            return `<input type="number" class="form-control form-control-sm" disabled>`;
        
        case 'file':
            return `<input type="file" class="form-control form-control-sm" disabled>`;
        
        case 'textarea':
            return `<textarea class="form-control form-control-sm" rows="2" disabled></textarea>`;
        
        case 'radio':
            let radioHtml = '<div>';
            field.options.slice(0, 2).forEach((option, i) => {
                radioHtml += `
                    <div class="form-check form-check-sm">
                        <input class="form-check-input" type="radio" disabled>
                        <label class="form-check-label small">${option}</label>
                    </div>
                `;
            });
            if (field.options.length > 2) {
                radioHtml += '<small class="text-muted">... dan lainnya</small>';
            }
            radioHtml += '</div>';
            return radioHtml;
        
        case 'select':
            return `<select class="form-select form-select-sm" disabled><option>-- Pilih ${field.label} --</option></select>`;
        
        default:
            return `<input type="text" class="form-control form-control-sm" disabled>`;
    }
}

function updateFieldCount() {
    document.getElementById('fieldCount').textContent = formFields.length;
}

function previewForm() {
    updatePreview();
    // Show success message
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = `
        <div class="alert alert-info alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
            <i class="fas fa-info-circle me-2"></i>Preview form berhasil diperbarui!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function saveForm() {
    const formName = document.getElementById('formName').value;
    const formDescription = document.getElementById('formDescription').value;

    if (!formName) {
        alert('Nama form harus diisi!');
        return;
    }

    if (formFields.length === 0) {
        alert('Form harus memiliki minimal 1 field!');
        return;
    }

    // Simulate saving to database
    const formData = {
        name: formName,
        description: formDescription,
        fields: formFields,
        created_at: new Date().toISOString()
    };

    console.log('Form Data to Save:', formData);
    
    // Show success message
    alert('Form berhasil disimpan!\n\nForm dapat digunakan untuk recruitment sekarang.');
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateFieldCount();
    
    // Add event listeners for form name and description
    document.getElementById('formName').addEventListener('input', updatePreview);
    document.getElementById('formDescription').addEventListener('input', updatePreview);
});