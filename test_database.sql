-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Feb 25. 19:41
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `test_database`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`, `updated_at`) VALUES
(1, 'admin@gmail.com', '$2y$12$3U1CPTWKfYKHwZgrVQSmKO5s2bS8OaUpnPThsLjP0lBYEg6oEluxq', '2025-02-25 15:18:48', '2025-02-25 15:18:48');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `appointments`
--

CREATE TABLE `appointments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `timeline` time NOT NULL,
  `status` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `appointments`
--

INSERT INTO `appointments` (`id`, `date`, `timeline`, `status`, `description`, `user_id`, `created_at`, `updated_at`) VALUES
(1, '1976-03-02', '15:54:49', 'confirmed', 'Eos voluptas magni nemo eaque quia.', 6, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(2, '1971-12-20', '02:58:40', 'canceled', 'Vero dolorum sit commodi explicabo facilis.', 9, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(3, '1981-04-10', '02:48:05', 'confirmed', 'Amet sint id voluptatum dignissimos quo eaque.', 8, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(4, '2013-01-08', '02:20:09', 'pending', 'Ullam tenetur eveniet rerum eligendi animi ut porro.', 6, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(5, '1980-12-22', '13:20:40', 'pending', 'Esse dolorem quo iste suscipit.', 7, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(6, '1993-02-08', '09:48:47', 'pending', 'Est nobis eos incidunt nobis.', 8, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(7, '1989-11-11', '10:43:53', 'canceled', 'Dolor reiciendis sequi eaque.', 9, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(8, '1999-06-12', '04:18:23', 'pending', 'Expedita ut repudiandae aliquam officiis cupiditate ut.', 7, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(9, '1976-06-23', '09:32:04', 'confirmed', 'Omnis error accusantium accusamus eum ipsam et.', 1, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(10, '2010-10-30', '14:07:35', 'pending', 'Velit sunt enim hic explicabo.', 7, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(11, '1972-05-04', '03:19:13', 'canceled', 'Iste est eveniet quia ullam sit repellendus suscipit.', 7, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(12, '2004-07-16', '18:44:14', 'pending', 'Voluptatum placeat placeat incidunt.', 1, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(13, '2014-04-12', '11:57:30', 'canceled', 'Est voluptates dolor vel ea est natus quas.', 7, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(14, '1980-07-28', '06:45:21', 'confirmed', 'Animi ad dolores non eos ut sit.', 4, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(15, '2010-03-21', '01:24:15', 'confirmed', 'Perferendis voluptas velit harum.', 1, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(16, '2019-06-21', '02:50:47', 'canceled', 'Quis soluta facere ducimus nobis ipsa.', 8, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(17, '2008-07-15', '04:09:04', 'canceled', 'Facilis eos qui ad nobis.', 8, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(18, '1995-06-03', '16:59:42', 'canceled', 'Reprehenderit facere quod ut qui blanditiis.', 3, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(19, '2013-12-15', '15:02:50', 'canceled', 'Et autem facilis illo dicta explicabo.', 5, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(20, '2010-02-22', '18:18:40', 'pending', 'Similique natus adipisci saepe.', 8, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(21, '2014-11-07', '00:44:07', 'confirmed', 'Est accusantium rem et perferendis.', 7, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(22, '1988-06-29', '10:10:21', 'confirmed', 'Et blanditiis laboriosam voluptatibus natus.', 7, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(23, '2019-12-26', '18:09:56', 'pending', 'Consequuntur eos fuga rerum eaque in.', 10, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(24, '2016-08-15', '02:17:21', 'pending', 'Veritatis est aut voluptatibus asperiores veritatis occaecati quas.', 10, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(25, '1993-01-30', '23:59:43', 'canceled', 'Fugit nemo nulla sunt.', 9, '2025-02-25 15:18:48', '2025-02-25 15:18:48');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `CompanyName` varchar(255) NOT NULL,
  `tax_number` varchar(255) NOT NULL,
  `registration_number` varchar(255) NOT NULL,
  `headquarters` varchar(255) NOT NULL,
  `contact_person` varchar(255) NOT NULL,
  `contact_number` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `clients`
--

INSERT INTO `clients` (`id`, `user_id`, `CompanyName`, `tax_number`, `registration_number`, `headquarters`, `contact_person`, `contact_number`, `created_at`, `updated_at`) VALUES
(1, 2, 'Lizeth Nicolas', '745745501', '115113520', '784 Kaleigh Roads\nWest Laverna, NV 76508-7886', 'Rhiannon Cormier DDS', '1-978-452-9407', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(2, 8, 'Camille Zboncak III', '651967430', '163774000', '929 Mae Curve Suite 627\nPort Aglaeburgh, NJ 64363', 'Thea Ritchie', '314-381-0784', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(3, 7, 'Kayli Hessel', '593984633', '896526901', '700 Therese Freeway Suite 208\nNakiatown, IL 69206', 'Felicita Hilpert DVM', '(912) 403-6051', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(4, 9, 'Rene Predovic', '591183545', '839787131', '80665 Kaden Centers\nTomborough, MD 04210-0189', 'Dr. Etha Trantow V', '575.318.7673', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(5, 10, 'Kian Will', '331289696', '639308798', '902 Lesly Keys Suite 448\nNorth Yvette, AZ 17381-8664', 'Mr. Keanu Bergstrom PhD', '+1.283.855.3894', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(6, 5, 'Anastasia Huels', '699620469', '684426534', '185 Elenor Orchard\nKaciemouth, VT 10391-2871', 'Soledad Kuhic', '+1 (872) 824-7903', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(7, 8, 'Ms. Erika Gleason', '808844046', '457929925', '59077 Kacie Shoals Suite 600\nGladycemouth, AL 05433', 'Dr. Ellie Labadie II', '+1-283-679-6596', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(8, 5, 'Fabiola Marvin', '402848112', '421139303', '45106 Jacobi Prairie\nGarlandmouth, ND 71822', 'Madge Wiegand V', '559.620.8931', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(9, 9, 'Myrtie Botsford', '385344013', '976584587', '63407 Asha Curve Apt. 077\nPort Lysanne, WA 48531', 'Ardith Hackett', '+1-845-549-1471', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(10, 5, 'Aiyana Witting', '492510233', '124429021', '83886 Neoma Dale Suite 501\nEast Raulfort, ID 83271-0377', 'Arjun Labadie', '+1-351-946-5758', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(11, 6, 'Mrs. Patricia Feeney III', '466237105', '650081187', '172 Huel Flats Apt. 201\nAufderharberg, SC 71024', 'Mrs. Antonetta Stoltenberg II', '+1-725-920-2678', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(12, 2, 'Shirley Gorczany Sr.', '600030900', '443146234', '5765 Stoltenberg Trail Suite 385\nNew Kraigmouth, UT 07534-0074', 'Breana Adams DDS', '1-856-729-6714', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(13, 8, 'Miss Amy Wehner Sr.', '854494732', '829788917', '8248 Lubowitz Trail\nNew Jerod, NV 49341', 'Casper Ullrich III', '254-832-6204', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(14, 7, 'Prof. Horace Marvin', '851812447', '194573882', '75044 Dicki Brook Apt. 212\nEast Torrey, AR 66892-8821', 'Maurice Jerde', '781.802.8462', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(15, 9, 'Abner Torp IV', '232407777', '199257434', '4077 Howell Keys\nSouth Reggie, ME 93073-8341', 'Mr. Carson Roberts', '(680) 871-8873', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(16, 8, 'Marley Hilpert', '534792491', '211724067', '449 Wiegand Freeway\nSouth Max, GA 95973', 'Bo Reinger', '531-384-6873', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(17, 1, 'Annabelle Turcotte', '611330608', '213263018', '216 Ken Alley\nLake Dena, CA 55196-5248', 'Dr. Garland Beer', '1-929-401-6431', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(18, 9, 'Dr. Wilfred Labadie', '963335060', '916873479', '12450 Leonel Manor\nPort Meaghan, VT 37813', 'Darby Kohler', '1-702-891-4497', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(19, 5, 'Dr. Arnold Schmidt II', '664945981', '679965577', '7278 Aurelia Courts Suite 946\nClairehaven, NV 00261', 'Mr. Taurean Bergstrom II', '1-706-431-6239', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(20, 2, 'Nedra Harvey II', '851456114', '758269022', '632 Reinger Skyway Apt. 360\nConsueloburgh, FL 17435-9959', 'Prof. Arno Weissnat MD', '+19298590467', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(21, 6, 'Dr. Rhea Kub DDS', '317296014', '238636604', '343 Katherine Isle\nPort Kobeburgh, NV 74704-5621', 'Landen Batz', '561.895.1781', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(22, 2, 'Jeremie Schaefer', '921069931', '980788955', '91823 Jazmyne Avenue\nLake Kristofer, IN 98652-4380', 'Abby Bartell', '+1 (640) 329-1559', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(23, 5, 'Kaia Davis', '483783280', '717114730', '4335 Antonette Walk\nWest Arianna, UT 95815-2377', 'Jasper Hodkiewicz', '(606) 980-8609', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(24, 9, 'Cierra Wilderman I', '792825967', '433428173', '20951 Maryjane Extension Suite 183\nRathfort, AR 44973', 'Kaylie Kilback', '984-682-0764', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(25, 7, 'Patrick Casper', '946581310', '705672767', '10489 Paul Mill Suite 962\nSouth Jailyn, OK 82168', 'Koby Watsica', '+1-270-605-3869', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(26, 5, 'Ron Schumm', '110134054', '232767640', '261 Aiyana Unions\nRomaguerachester, WA 29022-1606', 'Dean Leannon', '+1-786-337-8466', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(27, 6, 'Prof. Ansley Hessel MD', '336082325', '500848860', '665 Wilton Pines Apt. 926\nHackettside, RI 29356', 'Hilma Koepp', '480.546.8583', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(28, 8, 'Adam Wolff', '359661043', '757534800', '40791 Funk Lodge\nAngelberg, ID 44467-9328', 'Corene Becker PhD', '1-415-510-2052', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(29, 9, 'Sandra Zulauf', '820798607', '484638431', '46101 Konopelski Points\nLake Shaina, DC 59678', 'Mrs. Oma Gorczany', '234.776.5304', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(30, 3, 'Miss Stephanie Wolf Jr.', '740198853', '169795034', '1924 Christina Via\nNew Saraifort, HI 12661', 'Cale Price', '+1 (540) 583-7777', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(31, 5, 'Shana Fritsch', '840437185', '653948870', '561 Chance Centers\nSouth Adelestad, UT 02587', 'Annamae Sanford', '1-509-958-9885', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(32, 5, 'Prof. Mariano Harvey', '274624974', '428223317', '627 Sophie Knoll Apt. 199\nLorainebury, OH 95117', 'Leonora Ryan', '+1-660-731-7934', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(33, 7, 'April Hermiston', '799508802', '535901344', '245 Winifred Ports\nNew Katherynchester, MN 11319', 'Mrs. Henriette Hilpert Sr.', '765-790-3901', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(34, 10, 'Wilmer Ledner', '254554034', '232288214', '502 Bergstrom Mill\nHarveyland, WY 00223-0989', 'Andy Hill', '+1-727-208-6366', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(35, 8, 'Prof. Brent Volkman I', '803348454', '507248251', '467 Sauer Light\nNew Lela, NY 75777', 'Erwin Herzog', '(239) 371-6799', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(36, 9, 'Prof. General Lind IV', '950250656', '898642426', '51750 Presley Skyway\nLianamouth, HI 95506', 'Blanche Jakubowski', '(336) 670-2250', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(37, 7, 'Ernestina Schmidt', '125856991', '433388604', '444 Maverick Prairie Apt. 106\nRyleighborough, DC 77527-8350', 'Ms. Adah Schmidt', '(605) 680-3165', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(38, 5, 'Jeanie Friesen MD', '482262423', '523557196', '25262 Mills Knolls Apt. 301\nConsidineport, AR 47337', 'Ms. Elnora Adams PhD', '1-747-222-1667', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(39, 10, 'Kelton Kuvalis', '415975201', '765065695', '4109 Kassulke Forest\nStantonton, ID 00722', 'Mr. Anthony Stark I', '+1-434-307-5555', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(40, 8, 'Antwan Beer', '935731947', '669911893', '43970 Murphy Village Suite 306\nKevinchester, SD 16582', 'Ms. Angeline Nitzsche', '+1-762-737-7087', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(41, 1, 'Coby Ondricka', '497519546', '326682989', '6025 Akeem Views\nBahringerhaven, MO 76079', 'Mrs. Zola Rogahn V', '+1.754.743.7581', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(42, 5, 'Prof. Cruz Skiles', '615323779', '656043520', '87895 Raymundo Fields Suite 652\nRueckertown, FL 23350-6879', 'Emil Trantow I', '763-254-7767', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(43, 5, 'Miss Destiny Heidenreich V', '691045517', '726944201', '40904 Green Shoals\nPort Arielburgh, ME 65836-7955', 'Estel Ward', '+14694017324', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(44, 10, 'Beaulah Kuhlman', '785232450', '893744208', '1123 Green View Apt. 854\nPort Derick, ME 83091', 'Dr. Elmer Connelly', '(346) 897-7922', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(45, 1, 'Felipa Gorczany DDS', '232750444', '743411313', '59488 Pouros Path\nRueckermouth, TX 79357-4812', 'Dr. Nicola Monahan', '260.661.3663', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(46, 10, 'Elody Treutel', '232820479', '633420665', '55749 Wilderman Meadow\nGottliebside, NM 63685', 'Mrs. Shawna O\'Keefe Jr.', '1-820-442-4059', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(47, 8, 'Jerrod Lindgren', '953779255', '863439025', '48470 Schamberger Shoal Suite 108\nLake Adrianabury, NJ 52054', 'Prof. Lia Quigley', '(629) 616-5119', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(48, 7, 'Alexandra Lindgren', '873732716', '420229206', '9507 Albina Prairie\nPollichmouth, WI 83522-2121', 'Diana Wuckert', '+1 (360) 769-2364', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(49, 10, 'Mr. Werner McDermott IV', '300345095', '800776900', '348 Price Parks Suite 317\nWest Alfreda, MA 04880', 'Kayley Lockman', '(534) 793-7718', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(50, 2, 'Cathrine O\'Conner Sr.', '383454778', '671099345', '91347 Maureen Run Suite 589\nBoehmburgh, PA 15842-9810', 'Emiliano Pfannerstill II', '657.370.0482', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(51, 9, 'Prof. Jamar Fadel III', '713335133', '850049781', '12826 Denesik Brooks\nMrazland, WA 20805-9641', 'Lesly Runolfsdottir', '+1.786.404.1934', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(52, 10, 'Katelin Mraz', '155410864', '497703565', '706 Laila Lake\nPort Raulland, TN 39376', 'Price Braun DDS', '+1 (573) 463-4955', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(53, 7, 'Verla Grant', '996252497', '960846826', '493 Libby Valleys\nOleshire, HI 26226', 'Mr. Frankie Reilly', '(828) 788-8999', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(54, 2, 'Aurelia Stiedemann', '932859303', '533698922', '646 Lindgren Path Suite 770\nPort Laneymouth, RI 24640', 'Elinor Bahringer', '+16418783473', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(55, 1, 'Hilton Dare', '793219024', '899205776', '1961 Howe Avenue\nSouth Akeemchester, MS 34304', 'Dr. Darrick Miller', '1-980-522-9201', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(56, 6, 'Prof. Joe Robel', '340076249', '213789495', '3389 Macey Inlet\nKareemmouth, ME 24434-2713', 'Arthur Keeling III', '843.259.3276', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(57, 10, 'Miss Catharine Hoppe', '947543785', '739148917', '340 Dale View Apt. 145\nNew Michaelfort, TX 00694-8625', 'Mr. Anibal Erdman II', '947.404.8951', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(58, 2, 'Dayana Klein III', '215173707', '939984813', '59344 Corkery Gateway Suite 748\nKeaganport, MN 23801-4317', 'Nickolas Yundt', '+1.678.508.6734', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(59, 8, 'Jermain Lueilwitz', '103627662', '202062559', '25809 Vivien Trafficway\nNorth Haylie, NM 19412', 'Janis Glover Sr.', '+1 (737) 839-2632', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(60, 5, 'Lemuel Kulas', '236902770', '167631405', '9074 Nakia Unions\nPort Kolby, AK 71457-4090', 'Sheila Kirlin DVM', '260.845.8043', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(61, 8, 'Mrs. Myrtie Runte Sr.', '336994826', '152241997', '2181 McDermott Dale Apt. 768\nHettingerland, MA 25401-0591', 'Prof. Dovie Kuphal PhD', '870.625.0166', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(62, 7, 'Prof. Devan Koepp', '174805681', '951991281', '7597 Myrtis Park\nSouth Rahsaan, VT 02968', 'Dr. Zack Prohaska Sr.', '+1-281-595-9676', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(63, 7, 'Miss Marisol Gibson PhD', '604474787', '430771991', '618 Borer Junction\nLethaland, KS 58981-9028', 'Shakira Borer', '1-912-320-5930', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(64, 8, 'Jeremy Dooley', '438689726', '699460193', '1963 Ramon Brook\nWest Delorestown, SD 09245-7072', 'Federico Quigley', '1-580-640-8130', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(65, 8, 'Mrs. Lolita Hermann', '159048821', '394716957', '1959 Juvenal Haven Apt. 548\nNew Kathlyn, VT 37054', 'Harold Wehner II', '+1.609.740.6757', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(66, 5, 'Nicklaus Schowalter', '104516431', '305401262', '6924 Huels Circle\nPort Tyler, VA 25256-7291', 'Maximus Padberg', '+1-276-704-5528', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(67, 10, 'Taurean Ebert II', '109558241', '581192902', '91163 Ana Junctions\nPort Ociestad, NV 34097', 'Dr. Jacky Doyle PhD', '+1 (423) 309-1728', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(68, 2, 'Elfrieda Lindgren PhD', '927372985', '179068485', '1887 Rolfson Crossroad Apt. 305\nWest Chaunceyhaven, VA 49269', 'Hollis Bosco', '+1 (747) 263-6838', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(69, 8, 'Ms. Laisha Walsh', '998578988', '789274570', '91188 Pacocha Ways Apt. 675\nErnestbury, NJ 56294-9064', 'Baylee Senger', '+1 (802) 924-6213', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(70, 5, 'Bennie Jenkins', '592113645', '448024263', '806 Terry Station\nWest Elissatown, WY 55758-6136', 'Lucas Ortiz', '480.876.2961', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(71, 8, 'Jordy Bernier IV', '603355248', '509012964', '5660 Fabiola Square Apt. 116\nLauryntown, WA 80117', 'Juston Mante', '304-891-9675', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(72, 7, 'Elouise Heathcote', '891254139', '263463636', '34647 Kasey Fords\nTremblayfort, ID 69091', 'Anissa White', '(315) 215-6135', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(73, 6, 'Christelle Stoltenberg', '755171641', '333463617', '532 Blanda Rest Apt. 873\nLake Arnaldo, CO 62622-1868', 'Chester Bradtke', '240.757.0463', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(74, 5, 'Sandra Johnston', '766593340', '234166993', '730 Velma Extension Suite 851\nCedrickmouth, OK 32568', 'Mr. Constantin Hand II', '(458) 899-7490', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(75, 1, 'Cathrine Veum', '410667237', '454582596', '96132 Romaguera Unions\nPort Hymanton, NY 85038-3910', 'Ms. Kacie Nicolas', '+1.937.620.6801', '2025-02-25 15:18:48', '2025-02-25 15:18:48');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `data`
--

CREATE TABLE `data` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `DataName` varchar(255) NOT NULL,
  `DataFile` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(242, '2025_02_19_130708_create_personal_acces_token', 1),
(253, '0001_01_01_000000_create_users_table', 2),
(254, '0001_01_01_000001_create_cache_table', 2),
(255, '0001_01_01_000002_create_jobs_table', 2),
(256, '2025_01_19_093407_create_admins_table', 2),
(257, '2025_01_19_093742_create_appointments_table', 2),
(258, '2025_01_19_094205_create_clients_table', 2),
(259, '2025_01_19_094745_create_data_table', 2),
(260, '2025_01_19_094947_create_newsletters_table', 2),
(261, '2025_01_19_095329_create_service_users_table', 2),
(262, '2025_01_19_095330_create_services_table', 2);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `newsletters`
--

CREATE TABLE `newsletters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `newsletter_title` varchar(255) NOT NULL,
  `newsletter_status` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `newsletters`
--

INSERT INTO `newsletters` (`id`, `newsletter_title`, `newsletter_status`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Ella Hills IV', 1, 3, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(2, 'Ashtyn Schamberger', 0, 7, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(3, 'Mrs. Ardella Ondricka MD', 0, 5, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(4, 'Miss Karlee Kling IV', 1, 10, '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(5, 'Prof. Hannah Wintheiser', 0, 4, '2025-02-25 15:18:48', '2025-02-25 15:18:48');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `personal_acces_token`
--

CREATE TABLE `personal_acces_token` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `services`
--

CREATE TABLE `services` (
  `service_name` varchar(255) NOT NULL,
  `service_description` text NOT NULL,
  `service_price` decimal(10,2) NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `services`
--

INSERT INTO `services` (`service_name`, `service_description`, `service_price`, `service_id`) VALUES
('Pressure Vessel Inspector', 'Explicabo fugiat sit numquam dolor voluptatibus autem. Et aut quis quod. Et ut qui libero est id voluptatem praesentium. Excepturi illum ipsum non natus tenetur aut.', 62898.00, 2),
('Fire Investigator', 'Est esse perspiciatis omnis facere. In molestias ipsam necessitatibus perferendis. Deserunt soluta at et est autem aperiam consequatur. Placeat qui cumque magni aliquid velit.', 18856.00, 2),
('Legal Support Worker', 'Qui veritatis aut quam in culpa repudiandae molestias. Nulla autem debitis dolorem architecto esse.', 99224.00, 2),
('Sports Book Writer', 'Repudiandae sed corporis est autem. Beatae velit sunt quo vel iste. Optio veritatis quae reprehenderit eligendi voluptas molestiae excepturi. Et eaque laudantium a nesciunt quod.', 67817.00, 2),
('Protective Service Worker', 'Facilis animi non id quibusdam provident aspernatur nobis. Ut rerum et eos unde tenetur officia. Qui nisi odit voluptatem nihil.', 32009.00, 2),
('File Clerk', 'Officiis eius sunt eos atque. Error commodi animi harum rerum commodi minus. Facere molestias aliquam harum rerum.', 31835.00, 2),
('Musician', 'Molestias earum quia aut eaque voluptates optio. Quo et harum recusandae impedit culpa aut. Totam voluptatem optio illum nemo eius molestiae est repellat. Aut aut repellendus architecto id.', 27951.00, 2),
('Housekeeper', 'Tempora veritatis et suscipit et facilis velit voluptatem. Quia autem mollitia laborum nesciunt nam omnis praesentium. Esse error et mollitia inventore. Officia omnis aut quis dolorem error.', 19020.00, 2),
('Director Of Marketing', 'Qui eos asperiores eaque sunt dolores. Et ratione non eum repellendus qui consequuntur. Doloribus non quis delectus maxime. Cum officiis vel enim incidunt.', 21244.00, 2),
('Photographic Processing Machine Operator', 'Rerum aut enim et at. Similique minima aut omnis sed laudantium animi. Maxime repudiandae expedita dolor odit.', 76143.00, 2);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `service_users`
--

CREATE TABLE `service_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `service_users`
--

INSERT INTO `service_users` (`id`, `user_id`) VALUES
(5, 1),
(2, 2),
(6, 3),
(9, 3),
(10, 4),
(4, 5),
(3, 6),
(1, 7),
(7, 8),
(8, 9);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('tUCY91gZDd6lwoFbPJKVDLEh3XfFwbbDBQIEfk6Z', 79, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaTMwMlB1NUVVeERwQzV4dW5WbWxhUVdLbmVRdHZEVjNnTTk2SHg1NSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo3OTt9', 1740508886);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `role`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Mabelle Ferry', 'ozella.huel', 'wanda53@example.com', 'user', '2025-02-25 15:18:47', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'Xvpb7ZMsCI', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(2, 'Prof. Blaze Corwin', 'ken.stiedemann', 'frankie.schultz@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'NCCShd3KXn', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(3, 'Camila Klocko II', 'ayost', 'svon@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'JLAgKmBLkR', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(4, 'Mr. Carmel Borer', 'bette.daniel', 'abeier@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'NXjgCUiHQQ', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(5, 'Dr. Marquis Farrell II', 'leuschke.zackary', 'crona.alexandrine@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'G5CUJd1AJU', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(6, 'Constance Jaskolski', 'unique.block', 'anderson.ada@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'UDeFiy1DsD', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(7, 'Prof. Kelvin Mayert V', 'xmurazik', 'nikki72@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'gAVZB8G6e0', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(8, 'Amir Reilly PhD', 'cgreenholt', 'ymaggio@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 't7AWIJCcDh', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(9, 'Mr. Kaleb Marvin', 'kale18', 'troy24@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'ootzQYW3ve', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(10, 'Bruce Dicki', 'graham.sibyl', 'abshire.alexander@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'sSfvX3vG64', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(11, 'Mrs. Anastasia Jerde', 'lowe.braden', 'omiller@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'DoUMfUVgkE', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(12, 'Leilani Medhurst MD', 'cnikolaus', 'reichert.providenci@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'xeKBnQmC4e', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(13, 'Clementina Nikolaus', 'klabadie', 'rebecca.daniel@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'hPFo9KGW7V', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(14, 'Damaris Sauer', 'sylvia.friesen', 'greenholt.jannie@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '36S3rUONYn', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(15, 'Wilton Tromp', 'eoberbrunner', 'mmills@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'lrcAIZT9Tq', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(16, 'Ms. Rubye Daniel DVM', 'dhudson', 'lily.mckenzie@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'F49IRBaNiv', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(17, 'Matilda Heathcote DVM', 'zblock', 'kuphal.rebekah@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'MSKjNCxcQ7', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(18, 'Ms. Loma Johnson', 'greenfelder.jarrell', 'conrad.murphy@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'Y70NeyAWpf', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(19, 'Elouise Gaylord', 'sernser', 'timothy.sipes@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'qiP87ODEp1', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(20, 'Mr. Gay Jacobs I', 'jmcglynn', 'annette74@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '336LzDDOR5', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(21, 'Tania Heidenreich', 'uparker', 'sedrick.hyatt@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'ibUrxywoQg', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(22, 'Viviane Kautzer III', 'nrenner', 'tyree.dach@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '4fuln0UVEu', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(23, 'Myrtle Thiel III', 'gusikowski.tiana', 'wanda.corwin@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'Wf1EC2WC45', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(24, 'Dr. Finn Swift MD', 'rudy.thiel', 'durgan.christopher@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'UwFuEiPtrN', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(25, 'Ruben O\'Keefe', 'beatrice40', 'bernhard.russel@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '9VqAjuMoST', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(26, 'Makenzie Larson', 'metz.wilburn', 'rosamond.botsford@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'Jg6BgJDIzc', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(27, 'Mr. Isidro Von', 'hernser', 'vhowe@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'sItcLRcO6P', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(28, 'Dr. Olaf Goyette III', 'uupton', 'alowe@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'HDcNYrL2hI', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(29, 'Sarai Hagenes', 'lglover', 'colin.murazik@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'ImCgKQBaB6', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(30, 'Raymundo Hagenes', 'bschumm', 'romaguera.jessie@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'vfzTtsKvky', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(31, 'Lue Lemke DDS', 'rahsaan.grimes', 'kgraham@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'eyFN6iJDuK', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(32, 'Tiffany Stracke', 'jazmin.wehner', 'gmoore@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'ynIoq9VffG', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(33, 'Elisa Will', 'gbreitenberg', 'louvenia67@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'CdjitJ7LDw', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(34, 'Anya Satterfield V', 'hester13', 'tgleichner@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'XBsYiMSNtk', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(35, 'Christiana Kautzer', 'rbraun', 'rolfson.connor@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'tj79tWDdil', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(36, 'Ruth Mraz Sr.', 'kiley24', 'sframi@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'WOKPhJ8Snw', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(37, 'Mrs. Sierra Bernhard', 'hill.barry', 'reilly.celia@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'lgH5ckT8Gu', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(38, 'Isadore Yost', 'aiyana91', 'moore.mayra@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'UTRTWrtsgP', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(39, 'Leonora Mills', 'fahey.josiah', 'kzboncak@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '4XVrDXcK1V', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(40, 'Toni O\'Keefe', 'gia16', 'birdie.heaney@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'SB1JcMaYbm', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(41, 'Dr. Wilson Gusikowski', 'angus80', 'rconroy@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'EU2hTWjkmU', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(42, 'Unique Kreiger', 'candice17', 'bpowlowski@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'wNxuu5vMZV', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(43, 'Enola Schamberger', 'breitenberg.jarrod', 'rosenbaum.aylin@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'BperWQLJUn', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(44, 'Eunice Watsica Sr.', 'conroy.carlos', 'simonis.albina@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '7qPputjeQD', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(45, 'Xander Schultz DVM', 'meggie15', 'abe21@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'nl94zMoOh0', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(46, 'Neoma Streich I', 'qpadberg', 'heber09@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'pYm8TD9hZz', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(47, 'Joshuah Larkin', 'reynolds.florine', 'ismitham@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'Q7AN9emNFq', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(48, 'Kevon Thompson', 'selina.stark', 'ghodkiewicz@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'uSZsD5oNXu', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(49, 'Lavonne Hegmann', 'vivien98', 'plangosh@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'mrQn3MX8pf', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(50, 'Prof. Nicole Kertzmann MD', 'garnett.lowe', 'kilback.shanelle@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '51jj1usUvL', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(51, 'Margot Roberts', 'wehner.jannie', 'nicholaus16@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'O7XNTKNS5q', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(52, 'Miss Loma Tremblay', 'cassandre10', 'keon.buckridge@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'n4XVvYAnbv', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(53, 'Garnet Leannon', 'psauer', 'charles75@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'zZvvHYFEfk', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(54, 'Mavis Rutherford', 'therese.swaniawski', 'jacobson.mauricio@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'j89oeC4g95', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(55, 'Lennie Johnson', 'stoltenberg.mabelle', 'gupton@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'juvkyMclvk', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(56, 'Gregoria Klocko PhD', 'bahringer.lurline', 'matt76@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'xvq8iVu75j', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(57, 'Lia Rowe', 'margot.bradtke', 'snicolas@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'lhFLX2lql5', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(58, 'Gonzalo Koepp', 'nelda46', 'labadie.oma@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'OOB3gPQUok', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(59, 'Humberto O\'Connell', 'kuhic.luna', 'gardner.daugherty@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'U5rvX1KSeG', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(60, 'Annamae Weimann', 'rparisian', 'pfannerstill.tianna@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'QCkQhHPGrC', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(61, 'Cordie Corwin', 'chelsie67', 'astokes@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'zowuwR8NkX', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(62, 'Andreanne Schmeler', 'dylan.bartoletti', 'luigi.jenkins@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '612lNtGo62', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(63, 'Myrtis Kuphal', 'xbechtelar', 'zhowell@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'YBXMxAKQpP', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(64, 'Orland Orn', 'mbayer', 'walsh.kristin@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'q81I2Rlqp4', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(65, 'Susanna Reichel', 'marcellus.klein', 'flavie14@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'FcATOYfqS4', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(66, 'Mrs. Lisette Douglas PhD', 'jevon98', 'christian35@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'WrRe1vlu41', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(67, 'Chaim Mertz', 'hattie35', 'marks.lorenza@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '72YAhGLEGf', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(68, 'Mr. Salvador Rogahn III', 'oconner.bonita', 'rziemann@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'pA3yXTyupz', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(69, 'Prof. Keven Brown Jr.', 'kuhn.diamond', 'uriah.rutherford@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'JSdR1rpC08', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(70, 'Mr. Leonard McKenzie III', 'tosinski', 'tstrosin@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'xEc3E9oTgO', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(71, 'Mr. Madyson Boyer II', 'kemmer.anabel', 'wmraz@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'z04qFse1lL', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(72, 'Karen Wintheiser III', 'rmraz', 'carter.camila@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'sA3hA8yWBe', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(73, 'Destini Prosacco', 'ledner.terence', 'loraine47@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'jdUkac6ORD', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(74, 'Karli McGlynn', 'kuvalis.mathew', 'tracy.jakubowski@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'Byze820eko', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(75, 'Luna Goldner', 'dedrick.nitzsche', 'davon06@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'wqXQhRqSKa', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(76, 'Johathan Runolfsdottir', 'chaim.fadel', 'schmitt.lorenzo@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'AtFONo1kX5', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(77, 'Mr. Hipolito Jacobson PhD', 'bruen.ephraim', 'nblock@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'AohCCrxG6E', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(78, 'Prof. Jacynthe Rau', 'merl.sipes', 'mckenzie.ivory@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'iACMGkEX8W', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(79, 'Leonard Hammes', 'kshlerin.adelia', 'lyric.hilpert@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '1z2Uyg76qc', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(80, 'Jamarcus Kilback', 'noah66', 'nat.abbott@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '5XzLMyMVMv', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(81, 'Juliana Durgan', 'lorena87', 'donnelly.donavon@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'PDkztS8app', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(82, 'Mr. Hilton Nader PhD', 'sherman.cruickshank', 'anderson.carlie@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'RHI9pBM9bQ', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(83, 'Quinten Davis', 'kirlin.horace', 'esawayn@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'v677dUMDdA', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(84, 'Keshawn Mertz', 'powlowski.myrtle', 'alfonzo79@example.org', 'admin', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '7v5cbHgP765rHqMKLlOjjsVhOyVmnTYKy778uw85w3OXPI5ZBiseqW0wHFrx', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(85, 'Paula Davis', 'pprosacco', 'ovonrueden@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'A7wfcD7QOK', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(86, 'Ms. Deborah Marquardt III', 'phuels', 'kbarrows@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'oePn2Ni26V', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(87, 'Kayli Yundt', 'tabshire', 'gottlieb.toney@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'QTDqMbEPik', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(88, 'Eli Larson DVM', 'janelle51', 'iwuckert@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'JSSfWOMUlU', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(89, 'Prof. Carey Fay', 'chadd73', 'cole.tomas@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'm3JYsA1PQn', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(90, 'Mr. Maximus Fisher DDS', 'brandyn86', 'zrowe@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '6Er7nAYfQq', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(91, 'Mr. Armando Kutch MD', 'jody71', 'celestine.terry@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'CQ7k4IoqWD', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(92, 'Miss Vivianne Ryan DVM', 'pgoldner', 'hauer@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'gFhTEJHe6Q', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(93, 'Furman Altenwerth', 'terry.brisa', 'rahsaan76@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'CuXDTQKeQf', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(94, 'Jamar Bechtelar', 'deangelo82', 'khayes@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'KiBRqgfyru', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(95, 'Marjorie Heaney PhD', 'hintz.ocie', 'veronica.runolfsson@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'g8aeIY7GFs', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(96, 'Thalia Nolan', 'mkirlin', 'cornelius94@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '3pTfWW6xw1', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(97, 'Mr. Bertram Hermann', 'jeanette.rohan', 'xvolkman@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '9rWFPlJBK5', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(98, 'Dr. Jadyn Rogahn DDS', 'eleonore36', 'legros.paige@example.net', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'j2zYgHblaw', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(99, 'Prof. Deshaun Marquardt MD', 'brown.uriel', 'bell89@example.org', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', '7hoakrUMhp', '2025-02-25 15:18:48', '2025-02-25 15:18:48'),
(100, 'Mrs. Brooke Beier V', 'zboncak.alessandro', 'merritt41@example.com', 'user', '2025-02-25 15:18:48', '$2y$12$G5HgiiilYqaMdvbV5ZUPLetbrxq5F8RwljjBH3VuTy8zg5uf1kLb.', 'iUqxtJdGPe', '2025-02-25 15:18:48', '2025-02-25 15:18:48');

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_username_unique` (`username`),
  ADD UNIQUE KEY `admins_password_unique` (`password`);

--
-- A tábla indexei `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointments_user_id_foreign` (`user_id`);

--
-- A tábla indexei `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- A tábla indexei `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- A tábla indexei `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clients_user_id_foreign` (`user_id`);

--
-- A tábla indexei `data`
--
ALTER TABLE `data`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- A tábla indexei `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- A tábla indexei `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `newsletters`
--
ALTER TABLE `newsletters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `newsletters_user_id_foreign` (`user_id`);

--
-- A tábla indexei `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- A tábla indexei `personal_acces_token`
--
ALTER TABLE `personal_acces_token`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_acces_token_token_unique` (`token`),
  ADD KEY `personal_acces_token_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- A tábla indexei `services`
--
ALTER TABLE `services`
  ADD KEY `services_service_id_foreign` (`service_id`);

--
-- A tábla indexei `service_users`
--
ALTER TABLE `service_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_users_user_id_foreign` (`user_id`);

--
-- A tábla indexei `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT a táblához `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT a táblához `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT a táblához `data`
--
ALTER TABLE `data`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=263;

--
-- AUTO_INCREMENT a táblához `newsletters`
--
ALTER TABLE `newsletters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT a táblához `personal_acces_token`
--
ALTER TABLE `personal_acces_token`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `service_users`
--
ALTER TABLE `service_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Megkötések a táblához `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Megkötések a táblához `newsletters`
--
ALTER TABLE `newsletters`
  ADD CONSTRAINT `newsletters_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Megkötések a táblához `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `service_users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `service_users`
--
ALTER TABLE `service_users`
  ADD CONSTRAINT `service_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
