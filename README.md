Я переделал компонент для работы через сервер уведомлений <a href="comet-server.ru">comet-server.ru</a>. Работа через мой сервер gtsnotify.ru прекращается. Это не выгодно как оказалось.

Для многих сайтов требуются RealTime уведомления для работы чатов и мессенджеров, автоматической перезагрузки страницы при изменении данных на сервере и уведомлений пользователей о поступлении новых товаров, о, например, выпуске новой проды на книжных сайтах. Для этой цели разработан компонент gtsNotify для CMS MODX

<img src="https://file.modx.pro/files/8/3/1/831e0f8a8dc16b759579a2d2d2b49bd0.png" />

Для работы RealTime уведомлений требуется регистрация на <a href="comet-server.ru">comet-server.ru</a>.

В настройках компонента, в <strong>Пакеты->gtsNotify</strong> вбить секретный ключ и публичный id с <a href="comet-server.ru">comet-server.ru</a>.

<img src="https://file.modx.pro/files/f/a/0/fa06b57758375342da2086ffa393f4e6.png" />

С использованием этого компонента разработан компонент мессенджера для MODX - RealMessenger.

Компонент разработан с использованием pdoTools и bootstrap. Поддерживается bootstrap версии 3 и версии 4. Но можно стилизовать как вам угодно!

<b>Установка</b>
Для bootstrap v4 подключить на сайте Font Awesome Free 5.14.0 или другую версию, но, возможно, надо будет сменить иконки.
Установить с modstore.

В navbar сайта или где вам удобно разместить сниппет gtsNotify:
<code>{if $_modx->user.id > 1}{'!gtsNotify' | snippet}{/if}</code>

Для bootstrap v4, в системную настройку gtsnotify_frontend_css прописать [[+cssUrl]]web/b4_default.css.

<b>Использование</b>
В настройках компонента создаются каналы уведомлений:
<img src="https://file.modx.pro/files/b/8/a/b8a72fea467f675aabcde4f889887637.png" />
Нужно прописать Имя канала, Иконку, Чанк уведомления в меню канала и поставить Активно.
Для RealMessenger канал создается при его установке.

<b>API</b>
<code>
$gtsNotify = $modx->getService('gtsNotify', 'gtsNotify', MODX_CORE_PATH . 'components/gtsnotify/model/', []);
if ($gtsNotify) {
	$mess = [
		'message' => "{$message}",
	];
	if($notify = $this->gtsNotify->create_notify($mess)){
		$notify->addPurposeGroups('1,5,11','material_error');
		$notify->save();
		$notify->send();
	}
}
</code>
Функции назначения получателей:
<code>
$notify->addPurposeGroups(Группы пользователей MODX через запятую,Имя канала, Ссылка в уведомлении);
$notify->addPurpose($user_id,$channels,$url = '')
</code>
В браузере получаем сообщения:
<code>
document.addEventListener("gtsnotifyprovider", function(event) { 
	//console.log('notify',event.detail);
	for(var key in event.detail.channels) {
		if(key == 'RealMessenger'){
			user_data = event.detail.channels[key].data.user_data;
			for(var chat in user_data) {
				$el_chat = $('.realmessenger-chat[data-id="' + chat + '"]');
				$badge = $el_chat.find('.messages-new-count');
				$badge.text(user_data[chat].chat_count);
				if(user_data[chat].chat_count == 0){
					$badge.hide();
				}else{
					$badge.show();
					if($el_chat.hasClass("active")){
						$messages = $(event.detail.data.messages);
						$messages.removeClass('ownmessage');
						$('#realmessenger-messages').append($messages);
						var d = $('#realmessenger-messages');
						d.scrollTop(d.prop("scrollHeight"));
					}
				}
			}
		}
	}
});
</code>