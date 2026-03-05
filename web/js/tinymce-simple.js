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
            plugins: 'image link lists code',
            menubar: false, // Skrýt menu bar (první řádek)
            toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | image imagesgallery | iframe | socialembed | link | code',
        height: 500,
        automatic_uploads: true,
            file_picker_types: 'image',
            images_upload_url: '/admin/upload-image',
            document_base_url: window.location.origin,
            content_css: '/css/tinymce-content.css', // CSS pro zobrazení obsahu v editoru
            
            // Povolit script tagy pro Instagram a Twitter embed
            extended_valid_elements: 'script[src|async|defer|type|charset|id],blockquote[class|data-instgrm-permalink|data-instgrm-version]',
            
            // Omezení formátů - pouze Paragraph, H2, H3
            block_formats: 'Paragraph=p;Heading 2=h2;Heading 3=h3',
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
                // Přidání tlačítka pro vložení iframe
                editor.ui.registry.addButton('iframe', {
                    text: '📺 Iframe',
                    tooltip: 'Vložit iframe (Instagram, Twitter, YouTube, atd.)',
                    onAction: function() {
                        editor.windowManager.open({
                            title: 'Vložit iframe',
                            body: {
                                type: 'panel',
                                items: [
                                    {
                                        type: 'textarea',
                                        name: 'embed_code',
                                        label: 'Embed kód',
                                        placeholder: 'Vložte embed kód zde (z Instagramu: tři tečky → Embed, zkopírujte celý kód)',
                                        rows: 8
                                    },
                                    {
                                        type: 'htmlpanel',
                                        html: '<div style="margin-top: 10px; padding: 10px; background-color: #f8f9fc; border-radius: 8px; font-size: 0.9rem; color: #666;"><i class="fas fa-info-circle"></i> <strong>Jak získat embed kód:</strong><br>• <strong>Instagram:</strong> Tři tečky na příspěvku → Embed → zkopírujte celý kód<br>• <strong>Twitter:</strong> Tři tečky na tweetu → Embed Tweet → zkopírujte kód<br>• <strong>YouTube:</strong> Share → Embed → zkopírujte kód</div>'
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
                                let embedCode = data.embed_code.trim();
                                
                                if (!embedCode) {
                                    editor.windowManager.alert('Prosím vložte embed kód.');
                                    return;
                                }
                                
                                // Funkce pro zpracování embed kódu
                                function processEmbedCode(code) {
                                    // Pokud už je to kompletní embed kód (blockquote + script pro Instagram/Twitter, nebo iframe), použijeme ho tak jak je
                                    if (code.includes('<blockquote') || code.includes('<iframe')) {
                                        // Pro Instagram: použít kompletní embed kód (blockquote + script) - NEMĚNIT!
                                        if (code.includes('instagram.com') || code.includes('instagram-media') || code.includes('data-instgrm')) {
                                            // Zajistit, že je tam script pro Instagram embed
                                            if (!code.includes('instagram.com/embed.js')) {
                                                // Přidat script tag na konec
                                                code += '<script async src="https://www.instagram.com/embed.js"></script>';
                                            }
                                            // Vrátit kompletní kód tak jak je (blockquote + script)
                                            return code;
                                        }
                                        
                                        // Pro Twitter: použít kód tak jak je (blockquote + script)
                                        if (code.includes('twitter-tweet') || code.includes('twitter.com')) {
                                            // Zajistit, že je tam script pro Twitter widgets
                                            if (!code.includes('platform.twitter.com/widgets.js')) {
                                                code += '<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
                                            }
                                            return code;
                                        }
                                        
                                        // Pro YouTube nebo obecný iframe: použít tak jak je
                                        return code;
                                    }
                                    
                                    // Pokud je to jen URL, zkusíme vytvořit embed kód
                                    if (code.startsWith('http://') || code.startsWith('https://')) {
                                        // Instagram URL - vytvořit správný blockquote embed kód
                                        if (code.includes('instagram.com/p/') || code.includes('instagram.com/reel/')) {
                                            // Pro Instagram musíme použít blockquote, ne iframe
                                            // Pokud máme URL, vytvoříme blockquote s data-instgrm-permalink
                                            let postUrl = code.split('?')[0]; // Odstranit query parametry
                                            return '<blockquote class="instagram-media" data-instgrm-permalink="' + postUrl + '" data-instgrm-version="14" style=" background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,.5),0 1px 10px 0 rgba(0,0,0,.15); margin: 1px; max-width:540px; min-width:326px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);"><div style="padding:16px;"> <a href="' + postUrl + '" style=" background:#FFFFFF; line-height:0; padding:0 0; text-align:center; text-decoration:none; width:100%;" target="_blank"> <div style=" display: flex; flex-direction: row; align-items: center;"> <div style="background-color: #F4F4F4; border-radius: 50%; flex-grow: 0; height: 40px; margin-right: 14px; width: 40px;"></div> <div style="display: flex; flex-direction: column; flex-grow: 1; justify-content: center;"> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; margin-bottom: 6px; width: 100px;"></div> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; width: 60px;"></div></div></div><div style="padding: 19% 0;"></div> <div style="display:block; height:50px; margin:0 auto 12px; width:50px;"><svg width="50px" height="50px" viewBox="0 0 60 60" version="1.1" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g transform="translate(-511.000000, -20.000000)" fill="#000000"><g><path d="M556.869,30.41 C554.814,30.41 553.148,32.076 553.148,34.131 C553.148,36.186 554.814,37.852 556.869,37.852 C558.924,37.852 560.59,36.186 560.59,34.131 C560.59,32.076 558.924,30.41 556.869,30.41 M541,60.657 C535.114,60.657 530.342,55.887 530.342,50 C530.342,44.114 535.114,39.342 541,39.342 C546.887,39.342 551.658,44.114 551.658,50 C551.658,55.887 546.887,60.657 541,60.657 M541,33.886 C532.1,33.886 524.886,41.1 524.886,50 C524.886,58.899 532.1,66.113 541,66.113 C549.9,66.113 557.115,58.899 557.115,50 C557.115,41.1 549.9,33.886 541,33.886 M565.378,62.101 C565.244,65.022 564.756,66.606 564.346,67.663 C563.803,69.06 563.154,70.057 562.106,71.106 C561.058,72.155 560.06,72.803 558.662,73.347 C557.607,73.757 556.021,74.244 553.102,74.378 C549.944,74.521 548.997,74.552 541,74.552 C533.003,74.552 532.056,74.521 528.898,74.378 C525.979,74.244 524.393,73.757 523.338,73.347 C521.94,72.803 520.942,72.155 519.894,71.106 C518.846,70.057 518.197,69.06 517.654,67.663 C517.244,66.606 516.755,65.022 516.623,62.101 C516.479,58.943 516.448,57.996 516.448,50 C516.448,42.003 516.479,41.056 516.623,37.899 C516.755,34.978 517.244,33.391 517.654,32.338 C518.197,30.938 518.846,29.942 519.894,28.894 C520.942,27.846 521.94,27.196 523.338,26.654 C524.393,26.244 525.979,25.756 528.898,25.623 C532.057,25.479 533.004,25.448 541,25.448 C548.997,25.448 549.943,25.479 553.102,25.623 C556.021,25.756 557.607,26.244 558.662,26.654 C560.06,27.196 561.058,27.846 562.106,28.894 C563.154,29.942 563.803,30.938 564.346,32.338 C564.756,33.391 565.244,34.978 565.378,37.899 C565.522,41.056 565.552,42.003 565.552,50 C565.552,57.996 565.522,58.943 565.378,62.101 M570.82,37.631 C570.674,34.438 570.167,32.258 569.425,30.349 C568.659,28.377 567.633,26.702 565.965,25.035 C564.297,23.368 562.623,22.342 560.652,21.575 C558.743,20.833 556.562,20.326 553.369,20.18 C550.169,20.033 549.148,20 541,20 C532.853,20 531.831,20.033 528.631,20.18 C525.438,20.326 523.257,20.833 521.349,21.575 C519.376,22.342 517.703,23.368 516.035,25.035 C514.368,26.702 513.342,28.377 512.574,30.349 C511.834,32.258 511.327,34.438 511.181,37.631 C511.035,40.831 511,41.851 511,50 C511,58.147 511.035,59.17 511.181,62.369 C511.327,65.562 511.834,67.743 512.574,69.651 C513.342,71.625 514.368,73.299 516.035,74.965 C517.703,76.632 519.376,77.658 521.349,78.425 C523.257,79.167 525.438,79.673 528.631,79.82 C531.831,79.965 532.853,80.001 541,80.001 C549.148,80.001 550.169,79.965 553.369,79.82 C556.562,79.673 558.743,79.167 560.652,78.425 C562.623,77.658 564.297,76.632 565.965,74.965 C567.633,73.299 568.659,71.625 569.425,69.651 C570.167,67.743 570.674,65.562 570.82,62.369 C570.966,59.17 571,58.147 571,50 C571,41.851 570.966,40.831 570.82,37.631"></path></g></g></g></svg></div><div style="padding-top: 8px;"> <div style=" color:#3897f0; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:550; line-height:18px;">Zobrazit tento příspěvek na Instagramu</div></div><div style="padding: 12.5% 0;"></div></a><p style=" color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; line-height:17px; margin-bottom:0; margin-top:8px; overflow:hidden; padding:8px 0 7px; text-align:center; text-overflow:ellipsis; white-space:nowrap;"><a href="' + postUrl + '" style=" color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:normal; line-height:17px; text-decoration:none;" target="_blank">Příspěvek sdílený uživatelem @instagram</a></p></div></blockquote><script async src="https://www.instagram.com/embed.js"></script>';
                                        }
                                        // YouTube URL
                                        else if (code.includes('youtube.com/watch?v=') || code.includes('youtu.be/')) {
                                            let videoId = '';
                                            if (code.includes('youtube.com/watch?v=')) {
                                                videoId = code.split('v=')[1].split('&')[0];
                                            } else if (code.includes('youtu.be/')) {
                                                videoId = code.split('youtu.be/')[1].split('?')[0].split('&')[0];
                                            }
                                            
                                            if (videoId) {
                                                return '<iframe src="https://www.youtube.com/embed/' + videoId + '" width="100%" height="600" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
                                            }
                                        }
                                        // Twitter URL
                                        else if (code.includes('twitter.com/') || code.includes('x.com/')) {
                                            return '<blockquote class="twitter-tweet"><a href="' + code + '"></a></blockquote><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
                                        }
                                    }
                                    
                                    // Pokud nic nefunguje, vrátit původní kód
                                    return code;
                                }
                                
                                const processedCode = processEmbedCode(embedCode);
                                
                                // Vložení embed kódu do editoru
                                editor.insertContent(processedCode);
                                
                                // Přidat odstavec po iframe pro pokračování v psaní
                                setTimeout(function() {
                                    editor.insertContent('<p><br></p>');
                                    editor.selection.setCursorLocation(editor.getBody().lastChild, 0);
                                }, 100);
                                
                                api.close();
                            }
                        });
                    }
                });
                
                // Upravit image dialog pro podporu více souborů najednou
                editor.on('BeforeOpenDialog', function(e) {
                    if (e.dialog === 'image') {
                        // Po otevření dialogu přidat podporu pro více souborů
                        setTimeout(function() {
                            const fileInput = document.querySelector('.tox-dialog input[type="file"]');
                            if (fileInput) {
                                fileInput.setAttribute('multiple', 'multiple');
                                
                                // Přidat drag & drop do dialogu
                                const dialogBody = document.querySelector('.tox-dialog__body');
                                if (dialogBody) {
                                    dialogBody.addEventListener('dragover', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                    });
                                    
                                    dialogBody.addEventListener('drop', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        
                                        const files = e.dataTransfer.files;
                                        if (files.length > 0) {
                                            // Nahraj všechny soubory
                                            const uploadPromises = [];
                                            for (let i = 0; i < files.length && i < 4; i++) {
                                                const formData = new FormData();
                                                formData.append('file', files[i]);
                                                
                                                uploadPromises.push(
                                                    fetch('/admin/upload-image', {
                                                        method: 'POST',
                                                        body: formData
                                                    })
                                                    .then(response => response.json())
                                                    .then(result => {
                                                        if (result && result.location) {
                                                            // Vložit obrázek do editoru
                                                            editor.insertContent('<img src="' + result.location + '" alt="">');
                                                            return result.location;
                                                        }
                                                    })
                                                );
                                            }
                                            
                                            Promise.all(uploadPromises).then(function() {
                                                editor.windowManager.close();
                                            });
                                        }
                                    });
                                }
                                
                                // Upravit file input pro více souborů
                                fileInput.addEventListener('change', function(e) {
                                    const files = e.target.files;
                                    if (files.length > 0) {
                                        // Nahraj všechny soubory
                                        const uploadPromises = [];
                                        for (let i = 0; i < files.length && i < 4; i++) {
                                            const formData = new FormData();
                                            formData.append('file', files[i]);
                                            
                                            uploadPromises.push(
                                                fetch('/admin/upload-image', {
                                                    method: 'POST',
                                                    body: formData
                                                })
                                                .then(response => response.json())
                                                .then(result => {
                                                    if (result && result.location) {
                                                        // Vložit obrázek do editoru
                                                        editor.insertContent('<img src="' + result.location + '" alt="">');
                                                        return result.location;
                                                    }
                                                })
                                            );
                                        }
                                        
                                        Promise.all(uploadPromises).then(function() {
                                            editor.windowManager.close();
                                        });
                                    }
                                });
                            }
                        }, 300);
                    }
                });
                    // Přidání tlačítka pro galerii obrázků (2-4 obrázky vedle sebe)
                    editor.ui.registry.addButton('imagesgallery', {
                        text: '🖼️ Galerie',
                        tooltip: 'Vložit více obrázků vedle sebe (2-4 obrázky)',
                        onAction: function() {
                        let uploadedImages = [];
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
                            uploadedImages = [];
                            
                            // Vždy 4 inputy
                            for (let i = 1; i <= 4; i++) {
                                const uploadDiv = document.createElement('div');
                                uploadDiv.className = 'mb-3';
                                
                                const label = document.createElement('label');
                                label.textContent = 'Obrázek ' + i + ' (volitelné)';
                                label.className = 'form-label';
                                
                                const fileInput = document.createElement('input');
                                fileInput.type = 'file';
                                fileInput.accept = 'image/*';
                                fileInput.className = 'form-control';
                                fileInput.multiple = true; // Možnost vybrat více souborů
                                
                                const preview = document.createElement('div');
                                preview.id = 'preview-' + i;
                                preview.className = 'mt-2';
                                preview.style.minHeight = '50px';
                                preview.style.padding = '10px';
                                preview.style.border = '1px solid #e4e6ef';
                                preview.style.borderRadius = '8px';
                                preview.style.backgroundColor = '#f8f9fc';
                                preview.style.textAlign = 'center';
                                preview.style.color = '#b5b5c3';
                                preview.textContent = 'Přetáhněte obrázek sem nebo klikněte pro výběr';
                                
                                // Drag & drop
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
                                    if (files.length > 0) {
                                        // Pokud je více souborů, nahraj je do následujících polí
                                        const maxFiles = Math.min(files.length, 5 - i); // Maximálně do pole 4
                                        for (let j = 0; j < maxFiles; j++) {
                                            uploadImage(files[j], i + j);
                                        }
                                    }
                                });
                                
                                fileInput.addEventListener('change', function(e) {
                                    const files = e.target.files;
                                    if (files.length > 0) {
                                        // Pokud je více souborů, nahraj je do následujících polí
                                        const maxFiles = Math.min(files.length, 5 - i); // Maximálně do pole 4
                                        for (let j = 0; j < maxFiles; j++) {
                                            uploadImage(files[j], i + j);
                                        }
                                    }
                                });
                                
                                uploadDiv.appendChild(label);
                                uploadDiv.appendChild(fileInput);
                                uploadDiv.appendChild(preview);
                                uploadContainer.appendChild(uploadDiv);
                            }
                            
                            // Info text
                            const infoText = document.createElement('div');
                            infoText.className = 'mt-3';
                            infoText.style.fontSize = '0.9rem';
                            infoText.style.color = '#666';
                            infoText.innerHTML = '<i class="fas fa-info-circle"></i> Nahrajte 2-4 obrázky. Počet obrázků v galerii se určí podle počtu nahraných obrázků. Můžete přetáhnout obrázky nebo vybrat více najednou.';
                            uploadContainer.appendChild(infoText);
                        }
                        
                        const dialog = editor.windowManager.open({
                            title: 'Vložit galerii obrázků',
                            body: {
                                type: 'panel',
                                items: [
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
                                // Zjistit počet nahraných obrázků
                                const filledImages = uploadedImages.filter(img => img !== undefined && img !== null);
                                const imageCount = filledImages.length;
                                
                                if (imageCount < 2) {
                                    editor.windowManager.alert('Prosím nahrajte alespoň 2 obrázky pro galerii.');
                                    return;
                                }
                                
                                if (imageCount > 4) {
                                    editor.windowManager.alert('Galerie může obsahovat maximálně 4 obrázky.');
                                    return;
                                }
                                
                                // Vytvoření HTML struktury
                                const className = 'images-gallery-' + imageCount;
                                let html = '<div class="' + className + '">';
                                
                                filledImages.forEach(function(imgUrl) {
                                    html += '<img src="' + imgUrl + '" alt="">';
                                });
                                
                                html += '</div><p><br></p>';
                                
                                // Vložení do editoru
                                editor.insertContent(html);
                                
                                // Přesunout kurzor za nový odstavec
                                setTimeout(function() {
                                    editor.selection.setCursorLocation(editor.getBody().lastChild, 0);
                                }, 100);
                                
                                api.close();
                            }
                        });
                        
                        // Získání containeru a inicializace
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
                    text: '🔗 Embed',
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
                                
                                // Vložíme URL do editoru jako odstavec s odkazem
                                const content = '<p><a href="' + url + '">' + url + '</a></p>';
                                editor.insertContent(content);
                                
                                api.close();
                                editor.windowManager.alert('Odkaz byl vložen. Po uložení článku se automaticky zobrazí jako náhled (embed).');
                            }
                        });
                    }
                });
                // Nastavení jazyka pro kontrolu pravopisu při inicializaci
                editor.on('init', function() {
                    // Nastavit jazyk pro kontrolu pravopisu
                    const body = editor.getBody();
                    if (body) {
                        body.setAttribute('lang', 'cs');
                        body.setAttribute('spellcheck', 'true');
                    }
                });
                
                // Zajistit, že se jazyk nastaví při každé změně obsahu
                editor.on('change keyup', function() {
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