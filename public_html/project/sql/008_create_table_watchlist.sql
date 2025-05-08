CREATE TABLE IF NOT EXISTS `Watchlist` (
    `id` int auto_increment not null,
    `user_id` int,
    `movie_id` int not NULL,
    `created` timestamp default current_timestamp,
    `modified` timestamp default current_timestamp on update current_timestamp,
    PRIMARY KEY (`id`),
    UNIQUE (`user_id`, `movie_id`)
)