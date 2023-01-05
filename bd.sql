
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `ponto` (
  `id` int(11) NOT NULL,
  `usuario` varchar(150) NOT NULL,
  `distintivo` varchar(3) NOT NULL,
  `status` varchar(150) NOT NULL,
  `entrada` datetime NOT NULL,
  `saida` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ponto` (`id`, `usuario`, `distintivo`, `status`, `entrada`, `saida`) VALUES
(1, 'x', 'x', 'x', '2023-01-04 01:03:49', '2023-01-04 01:04:05'),


ALTER TABLE `ponto`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ponto`
  MODIFY `id` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;
