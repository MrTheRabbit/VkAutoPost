# VkAutoPost
Проект реализующий выкачивание контента с dashboard tumblr.com через API и его закачку на VK.

Дополнительно сохраняются в базе хеши файлов, id постов tumblr и репостов, чтобы контент не повторялся.

При помощи библиотеки libpuzzle реализован поиск дубликатов в базе по изображению.

Проект использует Laravel 5

Библиотеки, реализующие работу с API:
- app/Lib/VKAction.php
- app/Lib/TumblrAction.php
- app/Lib/ImgPuzzle.php
