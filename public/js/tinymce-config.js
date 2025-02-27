let script = document.createElement('script');
script.src = "https://cdn.tiny.cloud/1/l1vyo5rc4lr9bndoweby2luoq845e7lw20i4gb1rtwn0xify/tinymce/7/tinymce.min.js";
script.referrerPolicy = "origin";

script.onload = function() {
    tinymce.init({
        selector: '#editor',
        plugins: 'image link lists code',
        toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | image link | code',
        height: 500,
        automatic_uploads: true,
        file_picker_types: 'image',
        images_upload_url: '/admin/upload-image',

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
                        resolve(result.location);  // ✅ Správná cesta k obrázku
                    } else {
                        reject('Chybí "location" v odpovědi serveru.');
                    }
                })
                .catch(error => reject(`Chyba při uploadu: ${error.message}`));
            });
        },
    });
};

document.head.appendChild(script);
