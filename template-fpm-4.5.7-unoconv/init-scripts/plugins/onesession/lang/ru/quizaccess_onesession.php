<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Rule that blocks attempt to open same quiz attempt in other session
 *
 * @package    quizaccess_onesession
 * @copyright  2016 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['anothersession'] = 'Вы пытаетесь получить доступ к тесту с устройства отличного от того, с которого вы начали тест. Если это случилось из-за того, что Вы случайно закрыли браузер, пожалуйста, обратитесь к преподавателю.';
$string['eventattemptblocked'] = 'Попытка студента продолжить тестирование используя другое устройство была заблокирована';
$string['eventattemptunlocked'] = 'Студенту было разрешено продолжить попытку тестирования используя другое устройство';
$string['onesession'] = 'Блокировать одновременные подключения';
$string['onesession:unlockattempt'] = 'Снимать блокировку с попытки прохождения теста';
$string['onesession_help'] = 'При включени этого параметра пользователи смогут продолжать попытку тестирования только в том же сеансе браузера. Любые попытки открыть ту же самую попытку тестирования с другого компьютера, устройства или браузера будут заблокированы. Это может понадобиться, если Вы хотите быть уверены, что никто не помогает студенту, открыв ту же самую попытку тестирования на другом компьютере.';
$string['pluginname'] = 'Правило доступа к тесту: блокировка одновременных подключений';
$string['privacy:metadata'] = 'Плагин сохраняет хэш-сумму строки, используемой для идентификации сессии клиентского устройства. Хотя исходная строка содержит IP-адрес клиента и заголовок User-Agent, отправленный браузером клиента, хэш-сумма не позволяет извлечь эту информацию. Хэш-сумма автоматически удаляется сразу после завершения сеанса тестирования.';
$string['studentinfo'] = 'Внимание! Запрещено менять устройство во время сдачи этого теста. После начала тестирования любые попытки подключиться к этому тесту с другого компьютера, устройства или браузера будут блокироваться. Не закрывайте окно браузера до окончания тестирования, иначе вы не сможете завершить тест.';
$string['unlockthisattempt'] = 'Разрешить студенту продолжить эту попытку используя другое устройство';
$string['unlockthisattempt_header'] = 'Разблокировать попытку';
$string['whitelist'] = 'Сети без проверки IP';
$string['whitelist_desc'] = 'Этот параметр предназначен для уменьшения количества ложных срабатываний, когда студенты подключаются через мобильные сети, где IP-адрес может измениться в процессе тестирования. В большинстве ситуаций заполнять этот параметр не требуется. Вы можете указать несколько подсетей через запятую (например, «88.0.0.0/8, 77.77.0.0/16»). Если IP-адрес находится в таких сетях, он не будет проверяться. Чтобы полностью отключить проверку IP, Вы можете установить значение 0.0.0.0/0.';
