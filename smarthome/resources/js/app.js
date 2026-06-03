import './bootstrap';

// Exemple de script pour les boutons ON/OFF
// document.addEventListener('DOMContentLoaded', function () {

//     document.querySelectorAll('.card button').forEach(button => {

//         button.addEventListener('click', function () {

//             const card = button.closest('.card');
//             const dataDiv = card.querySelector('.data');
//             const icon = button.querySelector('i');

//             let state = dataDiv.textContent.trim();

//             if (state === 'ON') {
//                 dataDiv.textContent = 'OFF';
//                 dataDiv.classList.remove('text-green-500');
//                 dataDiv.classList.add('text-red-500');

//                 icon.classList.remove('bi-toggle-on', 'text-green-500');
//                 icon.classList.add('bi-toggle-off', 'text-red-500');

//                 alert('Lumière éteinte !');

//             } else if (state === 'OFF') {
//                 dataDiv.textContent = 'ON';
//                 dataDiv.classList.remove('text-red-500');
//                 dataDiv.classList.add('text-green-500');

//                 icon.classList.remove('bi-toggle-off', 'text-red-500');
//                 icon.classList.add('bi-toggle-on', 'text-green-500');

//                 alert('Lumière allumée !');

//             } else if (state === 'UP') {
//                 dataDiv.textContent = 'DOWN';
//                 dataDiv.classList.remove('text-green-500');
//                 dataDiv.classList.add('text-red-500');

//                 icon.classList.remove('bi-toggle-on', 'text-green-500');
//                 icon.classList.add('bi-toggle-off', 'text-red-500');

//                 alert('Volet baissé !');

//             } else {
//                 dataDiv.textContent = 'UP';
//                 dataDiv.classList.remove('text-red-500');
//                 dataDiv.classList.add('text-green-500');

//                 icon.classList.remove('bi-toggle-off', 'text-red-500');
//                 icon.classList.add('bi-toggle-on', 'text-green-500');

//                 alert('Volet levé !');
//             }

//         });

//     });

// });


setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) alert.style.display = 'none';
}, 3000);

// Script to toggle any modal with the data-type="modal" attribute and data-modal-target and close the modal when clicking outside of it but not when clicking inside the modal content
document.addEventListener('DOMContentLoaded', function () {
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function () {
            const targetModalId = this.getAttribute('data-modal-target');
            const targetModal = document.getElementById(targetModalId);
            if (targetModal) {
                targetModal.classList.remove('hidden');
            }
        });
    });

    // Close modal when clicking outside of it
    const modals = document.querySelectorAll('[data-type="modal"]');
    modals.forEach(modal => {
        modal.addEventListener('click', function (event) {
            if (event.target === this) {
                this.classList.add('hidden');
            }
        });
    });
});

//Script to send formdata to the server when clicking on a button with the class "send-command" and prevent the default form submission
document.addEventListener('DOMContentLoaded', function () {
    const commandButtons = document.querySelectorAll('.send-command');
    commandButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const form = this.closest('form');
            const formData = new FormData(form);
            fetch(form.action, {
                method: form.method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            }).then(response => {
                // recharger la page pour voir les changements
                location.reload();
            })
            .then(data => {
                console.log('Success:', data);
                // Optionally, you can add code here to update the UI based on the response
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        });
    });
});
