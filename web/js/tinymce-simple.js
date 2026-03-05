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
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; line-height: 1.6; color: #333; }
                img { max-width: 100%; height: auto; }
                figure.image { margin: 1em 0; display: table; }
                figure.image figcaption { text-align: center; color: #666; font-size: 0.9em; margin-top: 0.5em; display: table-caption; caption-side: bottom; }
                div[class*="images-gallery-"] { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin: 1em 0; }
                div[class*="images-gallery-"] img { width: 100%; height: 150px; object-fit: cover; border-radius: 4px; }
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
                // --- VLASTNÍ TLAČÍTKO PRO JEDNODUCHÉ VLOŽENÍ OBRÁZKU ---
                editor.ui.registry.addButton('simpleimage', {
                    icon: 'image',
                    tooltip: 'Vložit jeden obrázek',
                    onAction: function() {
                        // Proměnné pro uložení stavu
                        let selectedFile = null;

                        const dialog = editor.windowManager.open({
                            title: 'Vložit obrázek',
                            body: {
                                type: 'panel',
                                items: [
                                    {
                                        type: 'htmlpanel',
                                        html: `
                                            <div style="margin-bottom: 15px;">
                                                <label class="tox-label" style="display: block; margin-bottom: 5px;">Vyberte obrázek z počítače</label>
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    <label for="simple-image-upload" class="tox-button tox-button--secondary" style="cursor: pointer; display: inline-block;">
                                                        Vybrat soubor
                                                    </label>
                                                    <span id="file-name-display" style="color: #666; font-size: 0.9em; font-style: italic;">Žádný soubor nevybrán</span>
                                                </div>
                                                <input type="file" id="simple-image-upload" accept="image/*" style="display: none;">
                                                <div id="image-preview-container" style="margin-top: 10px; max-height: 200px; overflow: hidden; text-align: center; display: none;">
                                                    <img id="image-preview" src="" style="max-width: 100%; max-height: 200px; border-radius: 4px; border: 1px solid #ddd;">
                                                </div>
                                            </div>
                                        `
                                    },
                                    {
                                        type: 'input',
                                        name: 'alt',
                                        label: 'Popis obrázku (ALT) - důležité pro vyhledávače',
                                        placeholder: 'Např. Pohled na Sněžku'
                                    },
                                    {
                                        type: 'checkbox',
                                        name: 'caption',
                                        label: 'Zobrazit tento popis i pod obrázkem (jako titulek)'
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
                                    text: 'Vložit obrázek',
                                    primary: true
                                }
                            ],
                            onSubmit: function(api) {
                                if (!selectedFile) {
                                    editor.windowManager.alert('Prosím vyberte obrázek.');
                                    return;
                                }

                                const data = api.getData();
                                const altText = data.alt ? data.alt.trim() : '';
                                const showCaption = data.caption;

                                // Zobrazit loading stav (zablokovat dialog)
                                api.block('Nahrávám obrázek...');

                                const formData = new FormData();
                                formData.append('file', selectedFile);

                                fetch('/admin/upload-image', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(result => {
                                    if (result && result.location) {
                                        let content = '';
                                        
                                        if (showCaption && altText) {
                                            // Vložení s popiskem (HTML5 figure)
                                            content = `<figure class="image"><img src="${result.location}" alt="${altText}"><figcaption>${altText}</figcaption></figure>`;
                                        } else {
                                            // Jen obrázek
                                            content = `<img src="${result.location}" alt="${altText}">`;
                                        }

                                        editor.insertContent(content);
                                        
                                        // Nový řádek za obrázkem, aby se dalo psát dál
                                        editor.insertContent('<p>&nbsp;</p>');
                                        
                                        api.close();
                                    } else {
                                        api.unblock();
                                        editor.windowManager.alert('Chyba: Server nevrátil cestu k obrázku.');
                                    }
                                })
                                .catch(error => {
                                    api.unblock();
                                    editor.windowManager.alert('Chyba při nahrávání: ' + error.message);
                                });
                            }
                        });

                        // Inicializace inputu po otevření dialogu
                        setTimeout(() => {
                            const fileInput = document.getElementById('simple-image-upload');
                            const nameDisplay = document.getElementById('file-name-display');
                            const previewContainer = document.getElementById('image-preview-container');
                            const previewImage = document.getElementById('image-preview');

                            if (fileInput) {
                                fileInput.addEventListener('change', function(e) {
                                    if (e.target.files.length > 0) {
                                        selectedFile = e.target.files[0];
                                        nameDisplay.textContent = selectedFile.name;
                                        
                                        // Náhled obrázku
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            previewImage.src = e.target.result;
                                            previewContainer.style.display = 'block';
                                        }
                                        reader.readAsDataURL(selectedFile);
                                    }
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

                                // Doplnit https:// pokud chybí
                                if (!/^https?:\/\//i.test(url) && !/^mailto:/i.test(url) && !/^tel:/i.test(url) && !/^\//.test(url)) {
                                    url = 'https://' + url;
                                }

                                const linkAttrs = {
                                    href: url,
                                    target: '_blank',
                                    rel: 'noopener noreferrer' // Bezpečnostní standard pro _blank
                                };

                                if (isLink) {
                                    // Aktualizace existujícího odkazu
                                    editor.dom.setAttribs(selectedNode, linkAttrs);
                                    selectedNode.textContent = text || url;
                                    editor.selection.select(selectedNode);
                                } else {
                                    // Vložení nového odkazu
                                    const linkText = text || url;
                                    editor.insertContent(`<a href="${url}" target="_blank" rel="noopener noreferrer">${linkText}</a>`);
                                }

                                api.close();
                            }
                        });
                    }
                });

                // --- GALERIE OBRÁZKŮ (původní kód, zachován) ---
                editor.ui.registry.addButton('imagesgallery', {
                    icon: 'gallery',
                    tooltip: 'Galerie obrázků (více obrázků vedle sebe)',
                    onAction: function() {
                        let uploadedImages = []; // Resetovat pole při každém otevření
                        let uploadContainer = null;
                        
                        function uploadImage(file, index) {
                            const formData = new FormData();
                            formData.append('file', file);
                            
                            const preview = document.getElementById('preview-' + index);
                            preview.innerHTML = '<span style="color: #666;">Nahrávám...</span>';
                            
                            return fetch('/admin/upload-image', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result && result.location) {
                                    uploadedImages[index - 1] = result.location;
                                    preview.innerHTML = '<img src="' + result.location + '" style="max-width: 100%; max-height: 150px; border-radius: 4px; object-fit: contain;">';
                                    return result.location;
                                } else {
                                    preview.innerHTML = '<span style="color: red;">Chyba: Chybí "location" v odpovědi serveru.</span>';
                                    throw new Error('Chybí location v odpovědi');
                                }
                            })
                            .catch(error => {
                                preview.innerHTML = '<span style="color: red;">Chyba při nahrávání: ' + error.message + '</span>';
                                throw error;
                            });
                        }
                        
                        function createUploadFields() {
                            if (!uploadContainer) {
                                return;
                            }
                            
                            uploadContainer.innerHTML = '';
                            
                            // Pokud je pole uploadedImages prázdné nebo kratší než aktuální počet inputů,
                            // chceme začít s jedním prázdným inputem
                            const currentImagesCount = uploadedImages.filter(img => img).length;
                            const inputsToShow = Math.min(Math.max(currentImagesCount + 1, 1), 10);
                            
                            // Vytvoření inputů
                            for (let i = 1; i <= inputsToShow; i++) {
                                createSingleUploadField(i);
                            }
                            
                            // Tlačítko pro přidání další fotky
                            const addButtonContainer = document.createElement('div');
                            addButtonContainer.className = 'mt-3 text-center';
                            
                            const addButton = document.createElement('button');
                            addButton.type = 'button';
                            addButton.className = 'tox-button tox-button--secondary';
                            addButton.textContent = '+ Přidat další fotku';
                            addButton.style.width = '100%';
                            
                            addButton.onclick = function() {
                                const currentInputs = uploadContainer.querySelectorAll('.upload-field-group').length;
                                if (currentInputs < 10) {
                                    createSingleUploadField(currentInputs + 1);
                                } else {
                                    editor.windowManager.alert('Maximální počet fotek je 10.');
                                }
                            };
                            
                            if (inputsToShow < 10) {
                                addButtonContainer.appendChild(addButton);
                                uploadContainer.appendChild(addButtonContainer);
                            }
                        }

                        function createSingleUploadField(index) {
                            if (document.getElementById('upload-group-' + index)) return;

                            const uploadDiv = document.createElement('div');
                            uploadDiv.className = 'mb-3 upload-field-group';
                            uploadDiv.id = 'upload-group-' + index;
                            
                            const label = document.createElement('label');
                            label.textContent = 'Obrázek ' + index;
                            label.className = 'form-label';
                            
                            const fileInput = document.createElement('input');
                            fileInput.type = 'file';
                            fileInput.accept = 'image/*';
                            fileInput.className = 'form-control';
                            fileInput.multiple = true; 
                            
                            const preview = document.createElement('div');
                            preview.id = 'preview-' + index;
                            preview.className = 'mt-2';
                            preview.style.minHeight = '50px';
                            preview.style.padding = '10px';
                            preview.style.border = '1px solid #e4e6ef';
                            preview.style.borderRadius = '8px';
                            preview.style.backgroundColor = '#f8f9fc';
                            preview.style.textAlign = 'center';
                            preview.style.color = '#b5b5c3';
                            preview.style.display = 'flex';
                            preview.style.alignItems = 'center';
                            preview.style.justifyContent = 'center';
                            
                            if (uploadedImages[index-1]) {
                                preview.innerHTML = '<img src="' + uploadedImages[index-1] + '" style="max-width: 100%; max-height: 150px; border-radius: 4px; object-fit: contain;">';
                            } else {
                                preview.textContent = 'Přetáhněte obrázek sem nebo klikněte pro výběr';
                            }
                            
                            preview.addEventListener('dragover', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                preview.style.borderColor = '#4d5aea';
                                preview.style.backgroundColor = '#f0f1ff';
                            });
                            
                            preview.addEventListener('dragleave', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                preview.style.borderColor = '#e4e6ef';
                                preview.style.backgroundColor = '#f8f9fc';
                            });
                            
                            preview.addEventListener('drop', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                preview.style.borderColor = '#e4e6ef';
                                preview.style.backgroundColor = '#f8f9fc';
                                
                                const files = e.dataTransfer.files;
                                handleFiles(files, index);
                            });
                            
                            fileInput.addEventListener('change', function(e) {
                                const files = e.target.files;
                                handleFiles(files, index);
                            });

                            uploadDiv.appendChild(label);
                            uploadDiv.appendChild(fileInput);
                            uploadDiv.appendChild(preview);
                            
                            const btnContainer = uploadContainer.querySelector('.text-center');
                            if (btnContainer) {
                                uploadContainer.insertBefore(uploadDiv, btnContainer);
                            } else {
                                uploadContainer.appendChild(uploadDiv);
                            }
                        }

                        function handleFiles(files, startIndex) {
                            if (files.length > 0) {
                                const remainingSlots = 10 - startIndex + 1;
                                const filesToProcess = Math.min(files.length, remainingSlots);
                                
                                for (let i = 0; i < filesToProcess; i++) {
                                    const currentIndex = startIndex + i;
                                    if (!document.getElementById('upload-group-' + currentIndex)) {
                                        createSingleUploadField(currentIndex);
                                    }
                                    uploadImage(files[i], currentIndex);
                                }
                            }
                        }
                        
                        
                        const dialog = editor.windowManager.open({
                            title: 'Vložit galerii obrázků',
                            body: {
                                type: 'panel',
                                items: [
                                    {
                                        type: 'input',
                                        name: 'gallery_alt',
                                        label: 'Společný popis galerie (Alt text)',
                                        placeholder: 'Např. Závod Českého poháru v Peci pod Sněžkou'
                                    },
                                    {
                                        type: 'htmlpanel',
                                        html: '<div id="gallery-upload-container" style="margin-top: 15px;"></div>'
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
                                const filledImages = uploadedImages.filter(img => img !== undefined && img !== null);
                                const imageCount = filledImages.length;
                                
                                const data = api.getData();
                                const baseAlt = data.gallery_alt ? data.gallery_alt.trim() : '';
                                
                                if (imageCount < 2) {
                                    editor.windowManager.alert('Prosím nahrajte alespoň 2 obrázky pro galerii.');
                                    return;
                                }
                                
                                if (imageCount > 10) {
                                    editor.windowManager.alert('Galerie může obsahovat maximálně 10 obrázků.');
                                    return;
                                }
                                
                                // Vytvoření HTML struktury
                                const className = 'images-gallery-' + Math.min(imageCount, 4);
                                let html = '<div class="' + className + '" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">';
                                
                                filledImages.forEach(function(imgUrl, index) {
                                    const altText = baseAlt ? (baseAlt + ' - ' + (index + 1)) : '';
                                    html += '<img src="' + imgUrl + '" alt="' + altText + '" style="width: 100%; height: auto; object-fit: cover;">';
                                });
                                
                                html += '</div><p><br></p>';
                                editor.insertContent(html);
                                
                                setTimeout(function() {
                                    editor.selection.setCursorLocation(editor.getBody().lastChild, 0);
                                }, 100);
                                
                                api.close();
                            }
                        });
                        
                        setTimeout(function() {
                            uploadContainer = document.getElementById('gallery-upload-container');
                            if (uploadContainer) {
                                createUploadFields();
                            }
                        }, 200);
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
