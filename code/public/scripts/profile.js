const username = document.querySelector('.name-status')
if (document.querySelector('.alert-nick')){
    username.textContent = "Никнейм занят!"
    username.style.color = 'red';

}
function handleFileChange(event) {
    const fileInput = event.target;
    const fileName = fileInput.files[0].name;
    const fileNameElement = document.querySelector("#fileName");
    fileNameElement.textContent = fileName;
  }