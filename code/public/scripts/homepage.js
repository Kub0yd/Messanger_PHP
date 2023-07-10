
const btn = document.querySelector('#testBtn');

const contactForm = document.querySelectorAll('#contact-user');               //li для контакта юзера
const chatBtn =  document.querySelector('#chat-submit');                      //кнопка отправки сообщения
const textField = document.querySelector('#message');                         //input сообщения
const area = document.querySelector('#area');                                 //контейнер поля ввода и сообщения
const chat = document.querySelector('.chat-form');                            //форма отправки сообщения
const chatHistory = document.querySelector('.chat-history-container');        //контейнер истории переписки
const currentUser = document.querySelector('.current-user-id');               //поле с ником юзера
const createChatElementButton = document.querySelector('#create-group-chat'); //li "Создать групповой чат"
const createChatForm = document.querySelector('.create-group-chat');          //форма с полем и кнопкой создания группоовго чата
const showMore = document.querySelector('#message-count');                    //кнопка показать еще
const contactUsernameList = document.querySelectorAll('.contact-username');   //поле с именем контакта
let messagesCount = 7; // шаг отображаемых сообщений
//скрываем или показываем форму создания группового чата
createChatElementButton.addEventListener('click', () =>{

  createChatForm.hidden = createChatForm.hidden == true ? false : true;

})
//отслеживание ПКМ по сгенерированному блоку с сообщением
// Привязываем обработчик к родительскому контейнеру
chatHistory.addEventListener('contextmenu', handleRightClick);
// Функция-обработчик для клика правой кнопкой мыши
function handleRightClick(event) {
  event.preventDefault(); // Предотвращаем стандартное контекстное меню
  let targetElement = event.target;

  // Проверяем, что целевой элемент является <div> с классом "message-card"
  if (targetElement.classList.contains('message-card')) {
    const contextMenuElement = targetElement.parentElement.querySelector('#context-menu');
    //показываем/скрываем контекстное меню при нажатии ПКМ
    contextMenuElement.hidden = contextMenuElement.hidden == true ? false : true;
  }
}

//проверяем наличие контейнера с чатом
if (chat) {
    // Создание WebSocket-соединения
  let socket = new WebSocket('ws://localhost:8000');

  // Обработчик события открытия соединения
  socket.onopen = function () {
      
      let params = new URLSearchParams(document.location.search); //параметры из  url
      let contactUser = params.get("username");
      let groupChat = params.get("group_chat");
      
      let chatId = getChatId(); //id чата из url
      //выбираем параметры для запроса сокет серверу в зависимости от вида чата
      let chatParams = groupChat ? getGroupStoryParams(currentUser.textContent,chatId, messagesCount) : getStoryParams(currentUser.textContent,contactUser,chatId, messagesCount) 
      //запрашиваем у сервера историю чата
      socket.send(chatParams);
      //изменяем событие при нажатии кнопки Отправить
      chatBtn.addEventListener('click', function(event) {
        event.preventDefault(); // Отменяем стандартное поведение формы

        //получаем текст сообщения (без пробелов)
        let message = textField.value.trim();
        
        if (message === '') return; // Если сообщение пустое, ничего не отправляем
        
        // Отправляем сообщение на сервер с параметрами (тип сообщения, текст, id чата, от кого)
        socket.send(JSON.stringify({ type: 'message', text: message, chatId: getChatId(), fromUser: currentUser.textContent}));
        //и запрашиваем обновленную историю сообщений
        socket.send(chatParams);
        // Очищаем поле ввода сообщения
        textField.value = "";
      });
    //слушаем нажатие ЛКМ на контейнере истории, чтобы найти кнопку reply, кому reply и кнопку delete
    chatHistory.addEventListener('click', handleClick);
    
    function handleClick(event) {

      let targetElement = event.target;
      //проверям что кнопка reply
      if (targetElement.classList.contains('reply-button')) {
        //ищем контейнер для выпадающего списка
        const dropdownDiv = targetElement.closest('div');
        dropdownMenu = dropdownDiv.children[1];
        //создаем список
        let replyUl = document.createElement('ul');
        replyUl.className = 'list-group';
        //дублируем пользователей из контактов в список
        contactUsernameList.forEach( user => {
          let li = document.createElement('li');
          li.className = 'list-group-item replay-user';
          li.textContent = user.textContent;
          replyUl.appendChild(li);
        })
        dropdownMenu.appendChild(replyUl);
      //когда нажимаем на юзера из списка reply  
      }else if (targetElement.classList.contains('replay-user')){

        forUser = targetElement.textContent;                //получаем имя пользователя кому реплай
        //ищем карточку сообщения
        parentDiv = targetElement.closest('div');
        while (!parentDiv.classList.contains('message-card')){
          parentDiv = parentDiv.parentNode;
        }
        messageContext = parentDiv.nextSibling.textContent; //получаем текст сообщения
        //ищем поле от кого
        fromUserBlock = parentDiv.querySelector('.current-user-id'); 
        fromUser = fromUserBlock.textContent;               //получаем от кого сообщение
        messageId = parentDiv.parentNode.id;                //получаем id сообщения
        //отправляем на сервер (тип, текст сообщения, id чата, кому, от кого, кто инициировал реплай, id сообщения)
        socket.send(JSON.stringify({ type: 'reply', text: messageContext, chatId: getChatId(), for:forUser, from: fromUser, user:currentUser.textContent, id: messageId, amount:messagesCount}));
        
        //кнопка удалить:
      }else if (targetElement.classList.contains('delete-button')){
        
        parentDiv = targetElement.closest('div');
        while (!parentDiv.classList.contains('card-context')){
          parentDiv = parentDiv.parentNode;
        }
        fromUserBlock = parentDiv.querySelector('.current-user-id'); 
        fromUser = fromUserBlock.textContent;               //получаем от кого сообщение
        messageId = parentDiv.parentNode.id;                //получаем id сообщения
        socket.send(JSON.stringify({ type: 'delete', chatId: getChatId(), from: fromUser, user:currentUser.textContent, id: messageId}));
        socket.send(chatParams);
      }
    }
    //обрабатываем кнопку показать еще
    showMore.addEventListener('click', function(event) {
      //добавлем количество отображаемых сообщений
      messagesCount = messagesCount + 5;
      chatParams = groupChat ? getGroupStoryParams(currentUser.textContent,chatId, messagesCount) : getStoryParams(currentUser.textContent,contactUser,chatId, messagesCount)
      //запрашиваем историю сообщений
      socket.send(chatParams);
    })


  };
  // Обработчик события получения сообщения
  socket.onmessage = function (event) {
      let data = event.data;
      let jsonData = JSON.parse(data);
      //обрабатываем сообщения в зависимости от типа
      switch(jsonData.type){
        case 'chatHistory': // отображаем историю сообщений
          currentChat = getChatId();
          // костыль
          if (jsonData.chat_id == currentChat){
            //генерируем блоки с сообщениями
            displayMessages(jsonData.messages);
          }
          break;
        case 'error':  //обрабатываем ошибки
          alert(jsonData.message);
          break;
        case 'message':

          break;
        
          
      }
  };

  // Обработчик события закрытия соединения
  socket.onclose = function () {
      // Код, выполняемый при закрытии соединения
  };
}
//отображаем сообщения
function displayMessages(messages){
  //показываем кнопку Показать еще если сообщений больше 7
  if (messages.length >= 7){
    showMore.hidden = false;
  }
  //очищаем контейнер
  chatHistory.innerHTML = '';
  //формируем блок сообщения
  messages.forEach((message) => {
    // если есть параметр с отметкой пересланного сообщения, добавляем к карточке соответствующее поле 
    if (message.from_user != null){
      generateCardHeader(message.from_user);
    }
    //генерация блока с параметрами (текст, от кого, путь до аватарки, дата создания, id сообщения)
    generateMessageCard(message.text, message.username, message.avatar, message.create_date, message.message_id)
    
  })
}
//получаем id чата из url
function getChatId(){
  const chatUrl = window.location.pathname;
  let parts = chatUrl.split('/chat/');
  return parts[1];
}
//параметры для получения истории чата с контактом
function getStoryParams (username, contact, chatId, count){
  let params =JSON.stringify({ type: 'getStory', user: username, contact: contact, chat: chatId, amount: count});
  return params;
}
//параметры для получения истории группового чата
function getGroupStoryParams (username, chatId, count){
  let params =JSON.stringify({ type: 'getGroupStory', user: username, chat: chatId, amount: count});
  return params;
}
//генерация заголовка "Пересланное сообщение"
function generateCardHeader(from_user){
  const cardHeader = document.createElement('div');
  cardHeader.className = 'card-header';
  cardHeader.textContent = "Пересланное сообщение от " + from_user;
  chatHistory.appendChild(cardHeader);
}
//генерация карточки сообщения
function generateMessageCard(text, username, avatar, create_date, message_id){

  const div = document.createElement("div");
  div.className = "card border-dark mb-3 message-card";
  div.style.maxWidth = "90rem";
  div.id = "message-card";
  

  const cardBody = document.createElement("div");
  cardBody.className = "card-body text-dark message-card";
  cardBody.id = message_id;

  const row = document.createElement("div");
  row.className = "row message-card card-context";

  const col1 = document.createElement("div");
  col1.className = "col-1";

  const img = document.createElement("img");
  img.src = "/upload/" + avatar;
  img.className = "rounded-3";
  img.alt = "Avatar";
  img.style.width = "30px";
  col1.appendChild(img);

  const col4_user = document.createElement("div");
  col4_user.className = "col-4";

  const userParagraph = document.createElement("p");
  userParagraph.className = "current-user-id";
  userParagraph.textContent = username;
  col4_user.appendChild(userParagraph);

  const col4_date = document.createElement("div");
  col4_date.className = "col-4";

  const dateParagraph = document.createElement("p");
  dateParagraph.className = "mess-date";
  dateParagraph.textContent = create_date;
  col4_date.appendChild(dateParagraph);

  const col1_context = document.createElement("div");
  col1_context.className = "col-1";
  col1_context.id = "context-menu";
  col1_context.hidden = true;

  const btnGroup = document.createElement("div");
  btnGroup.className = "btn-group";

  const dropdownDiv = document.createElement("div");
  dropdownDiv.className = "dropdown show";

  const dropdownButton = document.createElement("a");
  dropdownButton.className = "btn btn-outline-secondary reply-button";
  dropdownButton.href = "#";
  dropdownButton.role = "button";
  dropdownButton.id = "dropdownMenuLink";
  dropdownButton.setAttribute("data-toggle", "dropdown");
  dropdownButton.setAttribute("aria-haspopup", "true");
  dropdownButton.setAttribute("aria-expanded", "false");
  
  var icon = document.createElement("i");
  icon.className = "bi bi-reply-fill reply-button";
  
  dropdownButton.appendChild(icon);
  dropdownDiv.appendChild(dropdownButton);
  
  var dropdownMenu = document.createElement("div");
  dropdownMenu.className = "dropdown-menu";
  dropdownMenu.setAttribute("aria-labelledby", "dropdownMenuLink");
  dropdownDiv.appendChild(dropdownMenu);
  const trashButton = document.createElement("button");
  if (currentUser.textContent == username){

  trashButton.type = "button";
  trashButton.className = "btn btn-outline-secondary delete-button";

  const trashIcon = document.createElement("i");
  trashIcon.className = "bi bi-trash3 delete-button";

  trashButton.appendChild(trashIcon);
  }
  btnGroup.appendChild(dropdownDiv);
  if (currentUser.textContent == username){
  btnGroup.appendChild(trashButton);
  }
  col1_context.appendChild(btnGroup);

  row.appendChild(col1);
  row.appendChild(col4_user);
  row.appendChild(col4_date);
  row.appendChild(col1_context);

  const messageParagraph = document.createElement("p");
  messageParagraph.className = "card-text message-card";
  messageParagraph.setAttribute("name", "message");
  messageParagraph.textContent = text;

  cardBody.appendChild(row);
  cardBody.appendChild(messageParagraph);

  div.appendChild(cardBody);
  chatHistory.appendChild(div);

}
//фокус на окне с чатом
window.addEventListener('DOMContentLoaded', function() {
  const hash = window.location.hash.substr(1); // Получаем идентификатор из URL
  if (hash) {
    const targetElement = document.querySelector("#"+hash);
    if (targetElement) {
      targetElement.focus(); // Устанавливаем фокус на целевой элемент
    }
  }
});