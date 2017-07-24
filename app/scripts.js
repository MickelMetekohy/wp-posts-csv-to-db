const submitButton = document.querySelector('button[type="button"]');
const csvField = document.querySelector('#csvfile');
const form = document.querySelector('form');

const startLoader = (e) => {
  e.preventDefault();
  submitButton.setAttribute('disabled', 'true');
  csvField.style.display = 'none';
  setTimeout( () => {
    form.submit();
  }, 50 );
  
}
submitButton.addEventListener('click', startLoader);
