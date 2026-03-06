// Jednoduchá TinyMCE konfigurace s vestavěnou kontrolou pravopisu prohlížeče
document.addEventListener('DOMContentLoaded', function() {
    // Počkáme na načtení TinyMCE
    const initEditor = function() {
        if (typeof tinymce === 'undefined') {
            setTimeout(initEditor, 100);
            return;
        }

        tinymce.init({
            selector: '#editor',
            plugins: 'image link lists code autosave', // Přidán autosave
            menubar: false,
            // Přidáno tlačítko restoredraft (obnovit koncept) hned na začátek
            toolbar: 'undo redo restoredraft | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | simpleimage imagesgallery | socialembed simplelink | code',
            height: 500,
            automatic_uploads: true,
            
            // --- BLBUVZDORNÁ OPATŘENÍ ---
            
            // 1. Zákaz změny velikosti objektů myší (aby nerozbili layout na mobilu)
            object_resizing: false,
            
            // CSS styly přímo pro editor (aby galerie vypadala hezky už tady)
            content_style: `
                @import url('https://fonts.googleapis.com/css2?family=Fira+Sans+Condensed:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

                body { font-family: 'Fira Sans Condensed', sans-serif; line-height: 1.6; color: #333; margin: 1rem; }
                
                h1, h2, h3, h4, h5, h6 {
                    font-weight: 300;
                    color: #f1008d;
                    text-transform: uppercase;
                }

                img { max-width: 100%; height: auto; }
                .img-fluid { max-width: 100%; height: auto; }
                figure.image { margin: 1em 0; display: table; }
                figure.image figcaption { text-align: center; color: #666; font-size: 0.9em; margin-top: 0.5em; display: table-caption; caption-side: bottom; font-weight: 500; font-style: italic; }
                
                /* Facebook-style gallery grid in editor (Minimalist) */
                div[class*="images-gallery-"] { display: grid !important; grid-template-columns: repeat(2, 1fr) !important; gap: 4px !important; margin: 1em 0 !important; width: 100% !important; border-radius: 0 !important; overflow: visible !important; background-color: #171925 !important; }
                div[class*="images-gallery-"] img { width: 100% !important; height: 100% !important; object-fit: cover !important; min-height: 150px; display: block; border-radius: 0 !important; transition: opacity 0.3s ease; }
                div[class*="images-gallery-"] img:hover { opacity: 0.6 !important; }
                
                /* Styly pro zdroj */
                .image-source, .gallery-source {
                    font-size: 14px;
                    color: #999;
                    font-style: italic;
                    text-align: center;
                    margin-top: 5px;
                    margin-bottom: 20px;
                    display: block;
                    width: 100%;
                }

                /* 1 fotka (v galerii) */
                div.images-gallery-1 { grid-template-columns: 1fr !important; }
                div.images-gallery-1 img { height: auto !important; max-height: 400px; }
                
                /* 3 fotky */
                div.images-gallery-3 { grid-template-columns: 1fr 1fr !important; grid-template-rows: 300px 200px !important; }
                div.images-gallery-3 img:nth-child(1) { grid-column: 1 / -1 !important; }
                
                /* 4+ fotky */
                div.images-gallery-4 { grid-template-columns: 1fr 1fr 1fr !important; grid-template-rows: 300px 150px !important; }
                div.images-gallery-4 img:nth-child(1) { grid-column: 1 / -1 !important; }
            `,
            
            // 2. Nastavení Autosave (záchrana práce)
            autosave_ask_before_unload: true, // Zeptat se před zavřením, pokud není uloženo
            autosave_interval: '30s', // Ukládat každých 30s lokálně
            autosave_prefix: '{path}{query}-{id}-', // Unikátní klíč pro uložení
            autosave_restore_when_empty: false,
            autosave_retention: '20m', // Držet koncept 20 minut
            
            // 3. Čištění vloženého textu (např. z Wordu)
            paste_data_images: false, // Zakázat vkládání obrázků ctrl+v (musí přes nahrávání)
            smart_paste: true, // Inteligentní vkládání
            
            // 4. Nastavení Odkazů (zjednodušení) - nahrazeno vlastním tlačítkem simplelink
            // Ponecháváme link plugin jen pro interní funkčnost
            
            // 5. Obrázky - toto nastavení se týká standardního pluginu, který ale nahrazujeme vlastním tlačítkem
            // Ponecháváme pro zpětnou kompatibilitu a interní funkce
            file_picker_types: 'image',
            images_upload_url: '/admin/upload-image',
            document_base_url: window.location.origin,
            content_css: '/css/tinymce-content.css',
            
            // Odstranit nechtěné formáty
            invalid_elements: 'h1,h4,h5,h6',
            formats: {
                // Povolené formáty
                bold: {inline: 'b'},
                italic: {inline: 'i'},
                underline: {inline: 'u'},
                removeformat: {}
            },
            // Vlastní style_formats pro dropdown - pouze tyto budou v dropdownu
            style_formats: [
                {title: 'Normální text', format: 'p'},
                {title: 'Nadpis 2', format: 'h2'},
                {title: 'Nadpis 3', format: 'h3'}
            ],
            
            // Lokalizace pro češtinu
            language: 'cs',
            language_url: 'https://cdn.tiny.cloud/1/l1vyo5rc4lr9bndoweby2luoq845e7lw20i4gb1rtwn0xify/tinymce/7/langs/cs.js',
            
            // Nastavení pro vestavěnou kontrolu pravopisu prohlížeče
            browser_spellcheck: true,
            
            // Nastavení jazyka pro kontrolu pravopisu
            content_language: 'cs',
            
            // Vlastní nastavení editoru
            setup: function(editor) {
                // --- VLASTNÍ TLAČÍTKO PRO JEDNODUCHÉ VLOŽENÍ OBRÁZKU (VYLEPŠENÉ) ---
                editor.ui.registry.addButton('simpleimage', {
                    icon: 'image',
                    tooltip: 'Vložit obrázek',
                    onAction: function() {
                        let uploadedImageUrl = '';
                        let dialogApi = null;
                        
                        function updateSubmitButton(enable) {
                            if (dialogApi) {
                                dialogApi.setEnabled('submit-btn', enable);
                                const statusEl = document.getElementById('upload-status');
                                if (statusEl) {
                                    statusEl.innerHTML = enable 
                                        ? '<div style="color: #28a745;">✅ Obrázek nahrán. Můžete vložit.</div>' 
                                        : '<div style="color: #007bff; font-weight: bold;">⏳ Nahrávám...</div>';
                                }
                            }
                        }

                        function uploadImage(file) {
                            const formData = new FormData();
                            formData.append('file', file);
                            
                            const preview = document.getElementById('image-preview');
                            preview.innerHTML = '<span style="color: #666;">Nahrávám...</span>';
                            updateSubmitButton(false);
                            
                            fetch('/admin/upload-image', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result && result.location) {
                                    uploadedImageUrl = result.location;
                                    preview.innerHTML = '<img src="' + result.location + '" style="max-width: 100%; max-height: 200px; border-radius: 4px; object-fit: contain;">';
                                    updateSubmitButton(true);
                                } else {
                                    throw new Error('Chybí location');
                                }
                            })
                            .catch(error => {
                                preview.innerHTML = '<span style="color: red;">Chyba: ' + error.message + '</span>';
                                updateSubmitButton(false);
                            });
                        }

                        dialogApi = editor.windowManager.open({
                            title: 'Vložit obrázek',
                            body: {
                                type: 'panel',
                                items: [
                                    {
                                        type: 'input',
                                        name: 'alt',
                                        label: 'Popis obrázku (Alt text)',
                                        placeholder: 'Popis obrázku pro vyhledávače'
                                    },
                                    {
                                        type: 'checkbox',
                                        name: 'show_caption',
                                        label: 'Zobrazit popis pod obrázkem (jako titulek)'
                                    },
                                    {
                                        type: 'input',
                                        name: 'source',
                                        label: 'Zdroj (autor/web)',
                                        placeholder: 'Např. ČTK, Instagram @uzivatel'
                                    },
                                    {
                                        type: 'htmlpanel',
                                        html: `
                                            <div class="mb-3">
                                                <label class="form-label">Vyberte obrázek</label>
                                                <input type="file" class="form-control" accept="image/*" id="single-image-input">
                                                <div id="image-preview" style="margin-top: 10px; min-height: 100px; border: 2px dashed #ccc; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #f8f9fc; cursor: pointer;">
                                                    <span style="color: #aaa;">Klikněte nebo přetáhněte obrázek sem</span>
                                                </div>
                                                <div id="upload-status" style="margin-top: 5px; min-height: 24px;"></div>
                                            </div>
                                        `
                                    }
                                ]
                            },
                            buttons: [
                                { type: 'cancel', text: 'Zrušit' },
                                { type: 'submit', name: 'submit-btn', text: 'Vložit', primary: true, disabled: true }
                            ],
                            onSubmit: function(api) {
                                const data = api.getData();
                                const altText = data.alt || '';
                                const showCaption = data.show_caption;
                                const source = data.source || '';
                                let content = '';
                                
                                const sourceHtml = source ? `<div class="image-source">Zdroj: ${source}</div>` : '';

                                if (uploadedImageUrl) {
                                    if (showCaption && altText) {
                                        content = `<figure class="image"><img src="${uploadedImageUrl}" alt="${altText}" class="img-fluid"><figcaption class="image-title">${altText}</figcaption></figure>${sourceHtml}`;
                                        editor.insertContent(content);
                                        
                                        const selectedNode = editor.selection.getNode();
                                        // Pokud jsme vložili zdroj, musíme najít poslední element
                                        const lastElement = sourceHtml ? editor.dom.getNext(selectedNode, 'div.image-source') : editor.dom.getParent(selectedNode, 'figure');
                                        
                                        if (lastElement) {
                                            const newP = editor.dom.create('p', {}, '<br>');
                                            editor.dom.insertAfter(newP, lastElement);
                                            editor.selection.setCursorLocation(newP, 0);
                                        }
                                    } else {
                                        content = `<img src="${uploadedImageUrl}" alt="${altText}" class="img-fluid">${sourceHtml}`;
                                        editor.insertContent(content);
                                        editor.insertContent('<p>&nbsp;</p>');
                                    }
                                }
                                api.close();
                            }
                        });

                        setTimeout(() => {
                            const input = document.getElementById('single-image-input');
                            const preview = document.getElementById('image-preview');
                            
                            if (input && preview) {
                                input.addEventListener('change', e => {
                                    if (e.target.files.length > 0) uploadImage(e.target.files[0]);
                                });
                                
                                preview.addEventListener('click', () => input.click());
                                
                                preview.addEventListener('dragover', e => {
                                    e.preventDefault();
                                    preview.style.borderColor = '#4d5aea';
                                    preview.style.background = '#eef0ff';
                                });
                                
                                preview.addEventListener('dragleave', e => {
                                    e.preventDefault();
                                    preview.style.borderColor = '#ccc';
                                    preview.style.background = '#f8f9fc';
                                });
                                
                                preview.addEventListener('drop', e => {
                                    e.preventDefault();
                                    preview.style.borderColor = '#ccc';
                                    preview.style.background = '#f8f9fc';
                                    if (e.dataTransfer.files.length > 0) uploadImage(e.dataTransfer.files[0]);
                                });
                            }
                        }, 100);
                    }
                });

                // --- VLASTNÍ TLAČÍTKO PRO JEDNODUCHÝ ODKAZ ---
                editor.ui.registry.addButton('simplelink', {
                    icon: 'link',
                    tooltip: 'Vložit/Upravit odkaz',
                    onAction: function() {
                        const selectedNode = editor.selection.getNode();
                        const isLink = selectedNode.nodeName === 'A';
                        const currentUrl = isLink ? editor.dom.getAttrib(selectedNode, 'href') : '';
                        const currentText = isLink ? selectedNode.textContent : editor.selection.getContent({format: 'text'});

                        editor.windowManager.open({
                            title: isLink ? 'Upravit odkaz' : 'Vložit odkaz',
                            body: {
                                type: 'panel',
                                items: [
                                    {
                                        type: 'input',
                                        name: 'url',
                                        label: 'URL adresa (odkaz)',
                                        placeholder: 'https://www.seznam.cz'
                                    },
                                    {
                                        type: 'input',
                                        name: 'text',
                                        label: 'Zobrazený text',
                                        placeholder: 'Klikněte zde'
                                    }
                                ]
                            },
                            initialData: {
                                url: currentUrl,
                                text: currentText
                            },
                            buttons: [
                                {
                                    type: 'cancel',
                                    text: 'Zrušit'
                                },
                                {
                                    type: 'submit',
                                    text: isLink ? 'Aktualizovat' : 'Vložit',
                                    primary: true
                                }
                            ],
                            onSubmit: function(api) {
                                const data = api.getData();
                                let url = data.url.trim();
                                const text = data.text.trim();

                                if (!url) {
                                    editor.windowManager.alert('Prosím zadejte URL adresu.');
                                    return;
                                }

                                if (!/^https?:\/\//i.test(url) && !/^mailto:/i.test(url) && !/^tel:/i.test(url) && !/^\//.test(url)) {
                                    url = 'https://' + url;
                                }

                                const linkAttrs = {
                                    href: url,
                                    target: '_blank',
                                    rel: 'noopener noreferrer'
                                };

                                if (isLink) {
                                    editor.dom.setAttribs(selectedNode, linkAttrs);
                                    selectedNode.textContent = text || url;
                                    editor.selection.select(selectedNode);
                                } else {
                                    const linkText = text || url;
                                    editor.insertContent(`<a href="${url}" target="_blank" rel="noopener noreferrer">${linkText}</a>`);
                                }

                                api.close();
                            }
                        });
                    }
                });

                // --- GALERIE OBRÁZKŮ (S MULTI-UPLOADEM A STATUS BAREM) ---
                editor.ui.registry.addButton('imagesgallery', {
                    icon: 'gallery',
                    tooltip: 'Galerie obrázků',
                    onAction: function() {
                        let uploadedImages = [null]; // Start s jedním prázdným slotem
                        let activeUploads = 0;
                        let dialogApi = null;
                        let editingNode = null;
                        let initialAlt = '';
                        let initialSource = '';
                        let uploadContainer = null;

                        const selectedNode = editor.selection.getNode();
                        const existingGallery = editor.dom.getParent(selectedNode, 'div[class*="images-gallery-"]');
                        
                        if (existingGallery) {
                            editingNode = existingGallery;
                            uploadedImages = []; // Vyčistit pro načtení
                            const imgs = existingGallery.querySelectorAll('img');
                            imgs.forEach((img, index) => {
                                uploadedImages.push(img.getAttribute('src'));
                                if (index === 0) {
                                    const alt = img.getAttribute('alt') || '';
                                    initialAlt = alt.replace(/ - \d+$/, '');
                                }
                            });
                            if (uploadedImages.length === 0) uploadedImages.push(null);
                            
                            // Zkusit najít zdroj (hned za galerií)
                            const nextNode = existingGallery.nextElementSibling;
                            if (nextNode && nextNode.classList.contains('image-source')) {
                                initialSource = nextNode.textContent.replace('Zdroj: ', '').trim();
                            }
                        }

                        function updateSubmitButton() {
                            if (dialogApi) {
                                const isUploading = activeUploads > 0;
                                dialogApi.setEnabled('submit-btn', !isUploading);
                                
                                const statusEl = document.getElementById('gallery-upload-status');
                                if (statusEl) {
                                    // Spočítat statistiku
                                    const finishedCount = uploadedImages.filter(img => img !== null).length;
                                    const totalCount = Math.max(finishedCount + activeUploads, uploadedImages.length - (uploadedImages[uploadedImages.length-1] === null ? 1 : 0) + activeUploads);
                                    // Fix total count logic to be robust
                                    const realTotal = finishedCount + activeUploads;
                                    
                                    if (isUploading) {
                                        statusEl.innerHTML = `<div style="color: #007bff; font-weight: bold; white-space: nowrap;">⏳ Nahrávám (${finishedCount}/${realTotal})</div>`;
                                    } else {
                                        if (finishedCount > 0) {
                                            statusEl.innerHTML = `<div style="color: #28a745; white-space: nowrap; font-weight: bold;">✅ Nahráno (${finishedCount}/${finishedCount})</div>`;
                                        } else {
                                            statusEl.innerHTML = editingNode 
                                                ? '<div style="color: #28a745; white-space: nowrap;">✏️ Režim úprav</div>' 
                                                : '<div style="color: #666; white-space: nowrap;">Připraveno (0/0)</div>';
                                        }
                                    }
                                }
                            }
                        }

                        function removeImage(index) {
                            uploadedImages.splice(index, 1);
                            if (uploadedImages.length === 0) uploadedImages.push(null);
                            createUploadFields();
                            updateSubmitButton(); // Aktualizovat status
                        }

                        function handleFiles(files, startIndex) {
                            if (files.length === 0) return;

                            const filesToUpload = [];
                            
                            for (let i = 0; i < files.length; i++) {
                                const targetIndex = startIndex + i;
                                if (targetIndex >= 10) break; 
                                
                                filesToUpload.push({ file: files[i], index: targetIndex });
                                
                                // Zajistit, že pole je dost dlouhé
                                while (targetIndex >= uploadedImages.length) {
                                    uploadedImages.push(null);
                                }
                                // Nastavit placeholder pro loading stav
                                uploadedImages[targetIndex] = 'loading';
                            }
                            
                            // Překreslit UI hned, aby byly vidět loadery
                            createUploadFields();
                            
                            filesToUpload.forEach(item => {
                                uploadImage(item.file, item.index);
                            });
                        }

                        function uploadImage(file, index) {
                            const formData = new FormData();
                            formData.append('file', file);
                            
                            activeUploads++;
                            updateSubmitButton();

                            return fetch('/admin/upload-image', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result && result.location) {
                                    uploadedImages[index] = result.location;
                                    createUploadFields();
                                    return result.location;
                                } else {
                                    throw new Error('Chybí location');
                                }
                            })
                            .catch(error => {
                                console.error(error);
                                uploadedImages[index] = null; // Reset na prázdný slot při chybě
                                createUploadFields();
                                // Zobrazit chybu
                                setTimeout(() => {
                                    const preview = document.getElementById('preview-' + (index + 1));
                                    if (preview) {
                                        preview.innerHTML = '<span style="color: red; font-size: 0.9em;">Chyba uploadu</span>';
                                        // Po chvilce vrátit do stavu dropzone (což už je, protože jsme dali null, ale text tam zůstal)
                                        // createUploadFields to vyřeší při příštím renderu, nebo to necháme takto
                                    }
                                }, 50);
                            })
                            .finally(() => {
                                activeUploads--;
                                updateSubmitButton();
                            });
                        }
                        
                        function createUploadFields() {
                            if (!uploadContainer) return;
                            uploadContainer.innerHTML = '';
                            
                            uploadedImages.forEach((imgUrl, index) => {
                                createSingleUploadField(index, imgUrl);
                            });
                            
                            // Zobrazit tlačítko jen pokud není plno a poslední slot není loading/obsazený
                            // (zjednodušeno: pokud je < 10, tlačítko tam je)
                            if (uploadedImages.length < 10) {
                                const btnDiv = document.createElement('div');
                                btnDiv.className = 'text-center mt-3';
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'tox-button tox-button--secondary';
                                btn.textContent = '+ Přidat další fotku';
                                btn.onclick = function() {
                                    uploadedImages.push(null);
                                    createUploadFields();
                                };
                                btnDiv.appendChild(btn);
                                uploadContainer.appendChild(btnDiv);
                            }
                        }

                        function createSingleUploadField(index, imgUrl) {
                            const uiIndex = index + 1;
                            const uploadDiv = document.createElement('div');
                            uploadDiv.className = 'mb-3 upload-field-group';
                            
                            const label = document.createElement('label');
                            label.textContent = 'Obrázek ' + uiIndex;
                            label.className = 'form-label';
                            label.style.fontWeight = 'bold';
                            label.style.fontSize = '0.9em';
                            
                            const fileInput = document.createElement('input');
                            fileInput.type = 'file';
                            fileInput.accept = 'image/*';
                            fileInput.className = 'form-control';
                            fileInput.multiple = true; 
                            fileInput.style.display = 'none'; // Skrýt standardní input
                            
                            // Custom dropzone UI
                            const preview = document.createElement('div');
                            preview.id = 'preview-' + uiIndex;
                            preview.className = 'dropzone';
                            preview.style.minHeight = '120px';
                            preview.style.border = '2px dashed #ccc';
                            preview.style.borderRadius = '8px';
                            preview.style.padding = '10px';
                            preview.style.display = 'flex';
                            preview.style.alignItems = 'center';
                            preview.style.justifyContent = 'center';
                            preview.style.cursor = 'pointer';
                            preview.style.position = 'relative';
                            preview.style.background = '#f8f9fc';
                            preview.style.transition = 'all 0.2s';
                            
                            if (imgUrl === 'loading') {
                                preview.style.border = '2px solid #007bff';
                                preview.style.background = '#eef0ff';
                                preview.innerHTML = `
                                    <div style="text-align:center; color: #007bff;">
                                        <div class="tox-spinner" style="margin: 0 auto 10px auto;"></div>
                                        <div style="font-weight:bold;">Nahrávám...</div>
                                    </div>
                                `;
                            } else if (imgUrl) {
                                preview.style.border = '1px solid #ddd';
                                preview.style.background = '#fff';
                                const img = document.createElement('img');
                                img.src = imgUrl;
                                img.style.maxWidth = '100%';
                                img.style.maxHeight = '180px';
                                img.style.borderRadius = '4px';
                                img.style.objectFit = 'contain';
                                img.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
                                
                                const removeBtn = document.createElement('button');
                                removeBtn.innerHTML = '×'; // Křížek
                                removeBtn.style.position = 'absolute';
                                removeBtn.style.top = '5px'; // Uvnitř boxu
                                removeBtn.style.right = '5px'; // Uvnitř boxu
                                removeBtn.style.background = 'rgba(220, 53, 69, 0.9)'; // Poloprůhledná červená
                                removeBtn.style.color = 'white';
                                removeBtn.style.border = 'none';
                                removeBtn.style.borderRadius = '50%';
                                removeBtn.style.width = '24px';
                                removeBtn.style.height = '24px';
                                removeBtn.style.fontSize = '20px'; // Větší font pro křížek
                                removeBtn.style.display = 'flex';
                                removeBtn.style.alignItems = 'center';
                                removeBtn.style.justifyContent = 'center';
                                removeBtn.style.paddingBottom = '2px'; // Optické vycentrování křížku
                                removeBtn.style.cursor = 'pointer';
                                removeBtn.style.boxShadow = '0 2px 4px rgba(0,0,0,0.2)';
                                removeBtn.style.zIndex = '10';
                                removeBtn.title = 'Odstranit obrázek';
                                removeBtn.onclick = (e) => { e.stopPropagation(); removeImage(index); };
                                
                                preview.appendChild(img);
                                preview.appendChild(removeBtn);
                            } else {
                                preview.innerHTML = `
                                    <div style="text-align:center; color: #888;">
                                        <div style="font-size: 24px; margin-bottom: 5px;">📂</div>
                                        <div>Klikněte nebo přetáhněte fotky</div>
                                        <div style="font-size: 0.8em; color: #aaa;">(Podpora více souborů)</div>
                                    </div>
                                `;
                                preview.onclick = (e) => { if(e.target.tagName !== 'BUTTON') fileInput.click(); };
                            }
                            
                            // Drag & Drop events
                            preview.addEventListener('dragover', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                preview.style.borderColor = '#4d5aea';
                                preview.style.backgroundColor = '#eef0ff';
                                preview.style.transform = 'scale(1.02)';
                            });
                            
                            preview.addEventListener('dragleave', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                preview.style.borderColor = imgUrl ? '#ddd' : '#ccc';
                                preview.style.backgroundColor = imgUrl ? '#fff' : '#f8f9fc';
                                preview.style.transform = 'scale(1)';
                            });
                            
                            preview.addEventListener('drop', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                preview.style.borderColor = imgUrl ? '#ddd' : '#ccc';
                                preview.style.backgroundColor = imgUrl ? '#fff' : '#f8f9fc';
                                preview.style.transform = 'scale(1)';
                                
                                const files = e.dataTransfer.files;
                                handleFiles(files, index);
                            });
                            
                            fileInput.addEventListener('change', e => {
                                const files = e.target.files;
                                handleFiles(files, index);
                            });

                            uploadDiv.appendChild(label);
                            uploadDiv.appendChild(fileInput);
                            uploadDiv.appendChild(preview);
                            uploadContainer.appendChild(uploadDiv);
                        }
                        
                        dialogApi = editor.windowManager.open({
                            title: editingNode ? 'Upravit galerii' : 'Vložit galerii',
                            body: {
                                type: 'panel',
                                items: [
                                    { type: 'input', name: 'gallery_alt', label: 'Popis galerie', placeholder: 'Popis' },
                                    { type: 'input', name: 'gallery_source', label: 'Zdroj (autor/web)', placeholder: 'Např. ČTK, Instagram @uzivatel' },
                                    { type: 'htmlpanel', html: '<div id="gallery-upload-status"></div><div id="gallery-upload-container" style="max-height: 400px; overflow-y: auto; padding: 5px;"></div>' }
                                ]
                            },
                            initialData: { gallery_alt: initialAlt, gallery_source: initialSource },
                            buttons: [
                                { type: 'cancel', text: 'Zrušit' },
                                { type: 'submit', name: 'submit-btn', text: editingNode ? 'Uložit' : 'Vložit', primary: true }
                            ],
                            onSubmit: function(api) {
                                const filledImages = uploadedImages.filter(img => img);
                                if (filledImages.length < 2) {
                                    editor.windowManager.alert('Galerie musí mít alespoň 2 obrázky.');
                                    return;
                                }
                                
                                const data = api.getData();
                                const baseAlt = data.gallery_alt || '';
                                const source = data.gallery_source || '';
                                const className = 'images-gallery-' + Math.min(filledImages.length, 4);
                                
                                let html = '<div class="' + className + '">';
                                filledImages.forEach((url, idx) => {
                                    const alt = baseAlt ? baseAlt + ' - ' + (idx+1) : '';
                                    html += `<img src="${url}" alt="${alt}" class="img-fluid">`;
                                });
                                html += '</div>';
                                
                                if (source) {
                                    html += `<div class="image-source">Zdroj: ${source}</div>`;
                                }
                                
                                html += '<p><br></p>';
                                
                                if (editingNode) {
                                    editor.undoManager.transact(() => {
                                        // Pokud existuje starý zdroj, odstranit ho
                                        const nextNode = editingNode.nextElementSibling;
                                        if (nextNode && nextNode.classList.contains('image-source')) {
                                            editor.dom.remove(nextNode);
                                        }
                                        
                                        editor.dom.replace(editor.dom.createFragment(html), editingNode);
                                    });
                                } else {
                                    editor.insertContent(html);
                                }
                                api.close();
                            }
                        });
                        
                        // DOM Hack pro přesun statusu do footeru - ROBUSTNÍ VERZE
                        setTimeout(() => {
                            const statusDiv = document.getElementById('gallery-upload-status');
                            const dialogEl = document.querySelector('.tox-dialog');
                            
                            if (statusDiv && dialogEl) {
                                const footer = dialogEl.querySelector('.tox-dialog__footer');
                                
                                if (footer) {
                                    // Stylování statusu
                                    statusDiv.style.marginTop = '0';
                                    statusDiv.style.marginRight = 'auto'; // Klíčové pro zarovnání doleva v flexboxu
                                    statusDiv.style.marginLeft = '10px';
                                    statusDiv.style.alignSelf = 'center';
                                    statusDiv.style.display = 'flex';
                                    statusDiv.style.alignItems = 'center';
                                    statusDiv.style.fontSize = '14px';
                                    statusDiv.style.flex = '1'; // Zabrat volné místo
                                    
                                    // Vložení na začátek footeru
                                    const footerStart = footer.querySelector('.tox-dialog__footer-start');
                                    if (footerStart) {
                                        footerStart.style.display = 'flex';
                                        footerStart.style.flex = '1'; // Aby start container zabral místo
                                        footerStart.appendChild(statusDiv);
                                    } else {
                                        // Fallback pokud footer-start neexistuje
                                        footer.insertBefore(statusDiv, footer.firstChild);
                                    }
                                }
                            }
                            
                            uploadContainer = document.getElementById('gallery-upload-container');
                            if (uploadContainer) {
                                createUploadFields();
                                updateSubmitButton();
                            }
                        }, 100);
                    }
                });

                // Přidání tlačítka pro vložení sociálních sítí (Social Embed)
                editor.ui.registry.addButton('socialembed', {
                    icon: 'comment-add',
                    tooltip: 'Vložit příspěvek ze sociálních sítí (Instagram, Twitter, Facebook, TikTok, YouTube, Strava, Reddit)',
                    onAction: function() {
                        editor.windowManager.open({
                            title: 'Vložit příspěvek ze sociálních sítí',
                            body: {
                                type: 'panel',
                                items: [
                                    {
                                        type: 'htmlpanel',
                                        html: '<p style="margin-bottom: 10px;">Vložte odkaz na příspěvek ze sociální sítě. Podporované sítě:</p><ul style="margin-bottom: 15px; font-size: 0.9em;"><li>Instagram (Post, Reel)</li><li>Twitter / X</li><li>Facebook (Post, Video)</li><li>TikTok</li><li>YouTube (Video, Shorts)</li><li>Strava (Activity)</li><li>Reddit</li><li>Threads</li><li>Pinterest</li></ul>'
                                    },
                                    {
                                        type: 'input',
                                        name: 'url',
                                        label: 'URL adresa příspěvku',
                                        placeholder: 'https://www.instagram.com/p/...'
                                    }
                                ]
                            },
                            buttons: [
                                {
                                    type: 'cancel',
                                    text: 'Zrušit'
                                },
                                {
                                    type: 'submit',
                                    text: 'Vložit',
                                    primary: true
                                }
                            ],
                            onSubmit: function(api) {
                                const data = api.getData();
                                const url = data.url.trim();
                                
                                if (!url) {
                                    editor.windowManager.alert('Prosím zadejte URL adresu.');
                                    return;
                                }
                                
                                const content = '<p><a href="' + url + '">' + url + '</a></p>';
                                editor.insertContent(content);
                                
                                api.close();
                                editor.windowManager.alert('Odkaz byl vložen. Po uložení článku se automaticky zobrazí jako náhled (embed).');
                            }
                        });
                    }
                });

                // Přidání klávesových zkratek
                
                // Ctrl+Shift+G pro galerii
                editor.addShortcut('meta+shift+g', 'Vložit galerii', function() {
                    const button = editor.ui.registry.getAll().buttons['imagesgallery'];
                    if (button && button.onAction) {
                        button.onAction();
                    }
                });

                // NOVÉ: Ctrl+Shift+I pro jednoduchý obrázek
                editor.addShortcut('meta+shift+i', 'Vložit obrázek', function() {
                    const button = editor.ui.registry.getAll().buttons['simpleimage'];
                    if (button && button.onAction) {
                        button.onAction();
                    }
                });
                
                // Ctrl+Shift+E pro social embed
                editor.addShortcut('meta+shift+e', 'Vložit příspěvek', function() {
                    const button = editor.ui.registry.getAll().buttons['socialembed'];
                    if (button && button.onAction) {
                        button.onAction();
                    }
                });

                // Ctrl+K pro jednoduchý odkaz (přepíše defaultní)
                editor.addShortcut('meta+k', 'Vložit odkaz', function() {
                    const button = editor.ui.registry.getAll().buttons['simplelink'];
                    if (button && button.onAction) {
                        button.onAction();
                    }
                });

                // Nastavení jazyka pro kontrolu pravopisu při inicializaci
                editor.on('init', function() {
                    const body = editor.getBody();
                    if (body) {
                        body.setAttribute('lang', 'cs');
                        body.setAttribute('spellcheck', 'true');
                    }
                });
            },

            images_upload_handler: function (blobInfo, progress) {
                return new Promise((resolve, reject) => {
                    const formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());

                    fetch('/admin/upload-image', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result && result.location) {
                            resolve(result.location);
                        } else {
                            reject('Chybí "location" v odpovědi serveru.');
                        }
                    })
                    .catch(error => reject(`Chyba při uploadu: ${error.message}`));
                });
            },
        });
    };

    initEditor();
});