-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 01, 2026 at 06:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kp_oli_portfolio`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `id` int(11) NOT NULL,
  `title_en` varchar(255) NOT NULL,
  `title_np` varchar(255) DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `description_np` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fas fa-trophy',
  `category` enum('political','development','international','social') DEFAULT 'political',
  `year` year(4) DEFAULT NULL,
  `importance_level` enum('high','medium','low') DEFAULT 'medium',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `achievements`
--

INSERT INTO `achievements` (`id`, `title_en`, `title_np`, `description_en`, `description_np`, `icon`, `category`, `year`, `importance_level`, `display_order`, `created_at`) VALUES
(2, 'Constitutional Reforms 2015', 'संवैधानिक सुधार २०१५', 'Led the historic process of drafting and implementing Nepal 2015 Constitution, establishing federalism, secularism, and inclusive democracy. Played crucial role in consensus building among political parties.', 'नेपालको २०१५ को संविधान मस्यौदा तयार पार्ने र कार्यान्वयन गर्ने ऐतिहासिक प्रक्रियाको नेतृत्व गरे, जसले संघीयतावाद, धर्मनिरपेक्षता र समावेशी लोकतन्त्र स्थापना गर्यो। राजनीतिक दलहरू बीच सहमति निर्माणमा निर्णायक भूमिका खेले।', 'fas fa-handshake', 'political', '2015', 'high', 1, '2025-12-26 10:30:27'),
(3, 'Major Infrastructure Development', 'प्रमुख पूर्वाधार विकास', 'Initiated and completed major infrastructure projects including highways, airports, and energy projects that boosted economic growth and national connectivity. Focused on strategic road networks and hydroelectric power.', 'महामार्ग, विमानस्थल र ऊर्जा परियोजनाहरू सहितका प्रमुख पूर्वाधार परियोजनाहरू सुरु गरे र पूरा गरे जसले आर्थिक वृद्धि र राष्ट्रिय जडान बढायो। रणनीतिक सडक नेटवर्क र जलविद्युत शक्तिमा केन्द्रित।', 'fas fa-road', 'development', '2018', 'high', 2, '2025-12-26 10:30:27'),
(4, 'Strengthened International Relations', 'अन्तर्राष्ट्रिय सम्बन्ध सुदृढीकरण', 'Strengthened Nepal foreign relations with neighboring countries and global partners, promoting economic cooperation and diplomacy. Established new diplomatic ties and enhanced existing relationships.', 'परराष्ट्रिय सम्बन्धहरू सुदृढीकरण गरे, आर्थिक सहयोग र कूटनीतिलाई प्रवद्र्धन गरे। नयाँ कूटनीतिक सम्बन्ध स्थापना गरे र विद्यमान सम्बन्धहरू सुदृढ गरे।', 'fas fa-handshake', 'international', '2019', 'high', 3, '2025-12-26 10:30:27'),
(5, 'Communist Party Unification', 'कम्युनिस्ट पार्टी एकीकरण', 'Played a pivotal role in merging communist factions to form the unified CPN (UML), creating a stronger political force in Nepal. This unification strengthened leftist politics in the country.', 'नेपालमा एक शक्तिशाली राजनीतिक शक्ति सिर्जना गर्दै कम्युनिस्ट धडाहरू एकीकृत गरेर नेकपा (एमाले) गठन गर्नेमा निर्णायक भूमिका खेले। यस एकीकरणले देशमा वामपन्थी राजनीति सुदृढ गर्यो।', 'fas fa-users', 'political', '2014', 'high', 4, '2025-12-26 10:30:27'),
(6, 'Social Welfare Programs', 'सामाजिक कल्याण कार्यक्रमहरू', 'Implemented various social welfare programs focusing on healthcare, education, and poverty alleviation. Ensured better access to social services for marginalized communities.', 'स्वास्थ्य, शिक्षा र गरीबी निवारणमा केन्द्रित विभिन्न सामाजिक कल्याण कार्यक्रमहरू कार्यान्वयन गरे। सीमान्तकृत समुदायहरूको लागि सामाजिक सेवाहरूमा राम्रो पहुँच सुनिश्चित गरे।', 'fas fa-hand-holding-heart', 'social', '2016', 'medium', 5, '2025-12-26 10:30:27'),
(7, 'Economic Growth Initiatives', 'आर्थिक वृद्धि पहलहरू', 'Introduced policies that significantly improved Nepal economic growth rate. Focused on tourism development, agricultural modernization, and industrial growth.', 'नेपालको आर्थिक वृद्धि दरमा महत्वपूर्ण सुधार ल्याउने नीतिहरू सुरु गरे। पर्यटन विकास, कृषि आधुनिकीकरण र औद्योगिक वृद्धिमा केन्द्रित।', 'fas fa-chart-line', 'development', '2020', 'medium', 6, '2025-12-26 10:30:27'),
(8, 'Disaster Management Leadership', 'प्रकोप व्यवस्थापन नेतृत्व', 'Provided strong leadership during natural disasters including earthquakes and floods. Ensured effective relief distribution and reconstruction efforts.', 'भूकम्प र बाढी सहितका प्राकृतिक प्रकोपहरूको बेला बलियो नेतृत्व प्रदान गरे। प्रभावकारी राहत वितरण र पुनर्निर्माण प्रयासहरू सुनिश्चित गरे।', 'fas fa-shield-alt', 'social', '2015', 'high', 7, '2025-12-26 10:30:27'),
(9, 'Educational Reforms', 'शैक्षिक सुधारहरू', 'Implemented educational reforms improving access to quality education across Nepal. Focused on technical education and vocational training programs.', 'नेपालभर गुणस्तरीय शिक्षामा पहुँच सुधार गर्ने शैक्षिक सुधारहरू कार्यान्वयन गरे। प्राविधिक शिक्षा र व्यावसायिक प्रशिक्षण कार्यक्रमहरूमा केन्द्रित।', 'fas fa-graduation-cap', 'social', '2017', 'medium', 8, '2025-12-26 10:30:27');

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `title_en` varchar(255) NOT NULL,
  `title_np` varchar(255) NOT NULL,
  `description_en` text DEFAULT NULL,
  `description_np` text DEFAULT NULL,
  `category` varchar(50) DEFAULT 'general',
  `activity_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `location_map` varchar(500) DEFAULT NULL,
  `priority` tinyint(4) DEFAULT 1 COMMENT '1=Low, 2=Medium, 3=High, 4=Critical',
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `image_url` varchar(500) DEFAULT NULL,
  `organizer` varchar(100) DEFAULT NULL,
  `attendee_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `title_en`, `title_np`, `description_en`, `description_np`, `category`, `activity_date`, `start_time`, `end_time`, `location`, `location_map`, `priority`, `status`, `image_url`, `organizer`, `attendee_count`, `created_at`, `updated_at`) VALUES
(1, 'Public Rally in Kathmandu', 'काठमाडौंमा सार्वजनिक र्याली', 'A large public rally addressing current political issues and development plans for the capital city.', 'राजधानी शहरको लागि वर्तमान राजनीतिक मुद्दाहरू र विकास योजनाहरू सम्बोधन गर्ने ठूलो सार्वजनिक र्याली।', 'public_event', '2025-01-15', '10:00:00', '14:00:00', 'Tundikhel, Kathmandu', 'https://maps.google.com/?q=Tundikhel,Kathmandu', 3, 'upcoming', 'https://s.yimg.com/ny/api/res/1.2/Iklv6wIuTOCb0ub.NaSJug--/YXBwaWQ9aGlnaGxhbmRlcjt3PTk2MDtoPTY0MA--/https://media.zenfs.com/en/ap.org/2efbca32e2d921cc2c9a71e726b33827', 'CPN (UML) Central Committee', 15000, '2025-12-30 15:03:48', '2025-12-30 17:11:44'),
(2, 'Meeting with Chinese Delegation', 'चिनियाँ प्रतिनिधिमण्डलसँग बैठक', 'High-level meeting with Chinese officials to discuss bilateral relations and infrastructure projects.', 'द्विपक्षीय सम्बन्ध र पूर्वाधार परियोजनाहरू छलफल गर्न चिनियाँ अधिकारीहरूसँग उच्चस्तरीय बैठक।', 'meeting', '2025-01-20', '14:00:00', '16:30:00', 'Prime Minister Office, Singha Durbar', 'https://maps.google.com/?q=Singha+Durbar,Kathmandu', 4, 'upcoming', 'https://news.cgtn.com/news/7741444d336b7a6333566d54/img/c38507d1-6835-405b-a3c3-0c6a4b2bd4f1.jpg', 'Ministry of Foreign Affairs', 25, '2025-12-30 15:03:48', '2025-12-30 17:09:49'),
(5, 'Health Camp in Remote Village', 'दुर्गम गाउँमा स्वास्थ्य शिविर', 'Free health checkup camp organized for villagers in remote areas of Karnali province.', 'कर्णाली प्रदेशको दुर्गम क्षेत्रका गाउँलेहरूको लागि आयोजना गरिएको निःशुल्क स्वास्थ्य जाँच शिविर।', 'health_camp', '2025-01-05', '08:00:00', '16:00:00', 'Rara, Mugu District', 'https://maps.google.com/?q=Rara+Lake,Mugu', 3, 'completed', 'https://th.bing.com/th/id/R.e21b11af700d772a24c5b4276109db83?rik=aztFyTtBazwt8A&pid=ImgRaw&r=0', 'Health Ministry', 1500, '2025-12-30 15:03:48', '2025-12-30 17:18:32'),
(6, 'Party Central Committee Meeting', 'पार्टी केन्द्रीय समितिको बैठक', 'Regular meeting of the party\'s central committee to discuss organizational matters.', 'संगठनात्मक विषयहरू छलफल गर्न पार्टीको केन्द्रीय समितिको नियमित बैठक।', 'party_meeting', '2025-01-30', '15:00:00', '18:00:00', 'Party Central Office, Balkhu', 'https://maps.google.com/?q=CPN+UML+Office,Balkhu', 4, 'upcoming', 'https://thehimalayantimes.com/uploads/imported_images/wp-content/uploads/2015/11/nepali-congress-cwc-meeting.jpg', 'CPN (UML)', 150, '2025-12-30 15:03:48', '2025-12-30 17:13:07'),
(7, 'Agricultural Development Program', 'कृषि विकास कार्यक्रम', 'Launch of new agricultural subsidy program for farmers in Terai region.', 'तराई क्षेत्रका किसानहरूको लागि नयाँ कृषि सब्सिडी कार्यक्रमको सुरुवात।', 'development', '2025-01-12', '10:00:00', '12:00:00', 'Janakpur, Dhanusha', 'https://maps.google.com/?q=Janakpur,Dhanusha', 2, 'ongoing', 'https://tse1.explicit.bing.net/th/id/OIP.IZ7w9rSy2dSsP6DFVnckUgHaHa?rs=1&pid=ImgDetMain&o=7&rm=3', 'Ministry of Agriculture', 300, '2025-12-30 15:03:48', '2025-12-30 17:03:16'),
(8, 'TV Interview on National Issues', 'राष्ट्रिय मुद्दामा टिभी अन्तर्वार्ता', 'Live television interview discussing current national issues and government policies.', 'वर्तमान राष्ट्रिय मुद्दाहरू र सरकारी नीतिहरू छलफल गर्दै प्रत्यक्ष टेलिभिजन अन्तर्वार्ता।', 'media', '2025-01-08', '19:30:00', '20:30:00', 'NTV Studio, Kathmandu', 'https://maps.google.com/?q=NTV,Sanepa', 3, 'completed', 'https://www.unv.org/sites/default/files/Nepal_EC%20interview%20with%20AP1%20TV.jpeg', 'Nepal Television', 5, '2025-12-30 15:03:48', '2025-12-30 17:16:39');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `last_login`, `created_at`) VALUES
(1, 'admin', '$2y$10$oj64/xqgfWyeMmExMZeZU.I5j2z1y/mp9I5IdXtEw4hL134k2KETq', NULL, '2025-12-22 05:50:43');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('superadmin','admin','editor') DEFAULT 'editor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `email`, `full_name`, `role`, `created_at`, `last_login`) VALUES
(1, 'admin', '$2y$10$YourHashedPasswordHere', 'admin@kpoli.com', 'Site Administrator', 'superadmin', '2025-12-22 07:15:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `mobile_number` varchar(20) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `purpose` enum('Personal Issue','Community Problem','Development Project','Party Work','Other') NOT NULL,
  `preferred_date` date DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `full_name`, `mobile_number`, `address`, `purpose`, `preferred_date`, `message`, `status`, `submitted_at`, `updated_at`) VALUES
(2, 'Jane Smith', '9876543211', 'Pokhara, Nepal', 'Community Problem', '2025-12-26', 'Community development issues.', 'approved', '2025-12-25 08:53:54', '2025-12-25 08:53:54'),
(3, 'niraj', '9864499368', 'sandhikharka', 'Personal Issue', '2026-01-01', 'hello', 'rejected', '2025-12-25 09:42:40', '2025-12-25 09:42:55'),
(4, 'niraj', '9864499368', 'sandhikharka', 'Community Problem', '2026-01-01', 'bvgvhjhujk', 'rejected', '2025-12-26 07:27:48', '2025-12-26 07:49:40'),
(5, 'roomsewa', '9864499368', 'sandhikharka', 'Community Problem', '2025-12-30', 'hello', 'pending', '2025-12-28 05:39:14', '2025-12-28 05:39:14'),
(6, 'Deepak Khadka', '9848427724', 'Kailali', 'Party Work', '2026-01-01', 'Meeting ko lagoi', 'approved', '2025-12-31 11:14:09', '2025-12-31 11:16:34');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `is_read`, `submitted_at`) VALUES
(1, 'niraj', 'bikash@gmail.com', 'fdsa', 'gyghgyu', 0, '2025-12-22 06:13:55'),
(2, 'niraj', 'bikash@gmail.com', 'fdsa', 'riojfjdifiojv', 0, '2025-12-22 06:28:53'),
(3, 'niraj', 'bikash@gmail.com', 'fdsa', 'tyrh', 0, '2025-12-22 10:21:32'),
(4, 'niraj', 'niraj@gmail.com', 'fdsa', 'yiuyiu', 1, '2025-12-22 10:25:00'),
(5, 'niraj', 'niraj@gmail.com', 'fdsa', 'gghj', 1, '2025-12-24 08:07:54'),
(6, 'Vivek Khadka', 'acb@yahoo.com', 'hello test', '123', 1, '2025-12-24 09:34:56'),
(7, 'Dipendra BC', 'dipen@gail.com', 'Hello', 'hajur namaskar', 1, '2025-12-31 11:13:33');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `donor_name` varchar(100) DEFAULT NULL,
  `donor_email` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `transaction_proof` varchar(255) DEFAULT NULL,
  `screenshot_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `phone` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `transaction_id`, `donor_name`, `donor_email`, `amount`, `transaction_proof`, `screenshot_path`, `status`, `phone`, `message`, `created_at`, `verified_at`) VALUES
(1, 'TXN17664008798109', '', '', 1000.00, NULL, '../uploads/screenshots/donation_1766400879_3796.jpeg', 'verified', '', NULL, '2025-12-22 10:54:39', '2025-12-22 10:55:25'),
(2, 'TXN17664275975415', '', '', 1000656.00, NULL, '../uploads/screenshots/donation_1766427597_1024.jpeg', 'pending', '', NULL, '2025-12-22 18:19:57', NULL),
(3, 'TXN17664931627948', '', '', 1000.00, NULL, '../uploads/screenshots/donation_1766493162_5434.jpg', 'verified', '', NULL, '2025-12-23 12:32:42', '2025-12-23 15:06:33'),
(4, 'TXN17664990622353', '', '', 1000.00, NULL, '../uploads/screenshots/donation_1766499062_7830.jpg', 'verified', '', NULL, '2025-12-23 14:11:02', '2025-12-23 14:21:47'),
(5, 'TXN17665049372024', '', '', 1000.00, NULL, '../uploads/screenshots/donation_1766504937_7187.jpg', 'rejected', '', NULL, '2025-12-23 15:48:57', NULL),
(6, 'TXN17665636864329', '', '', 1000.00, NULL, '../uploads/screenshots/donation_1766563686_9222.jpeg', 'verified', '', NULL, '2025-12-24 08:08:06', '2025-12-24 09:32:47'),
(7, 'TXN17665693941753', '', '', 500000.00, NULL, '../uploads/screenshots/donation_1766569394_9419.jpeg', 'verified', '', NULL, '2025-12-24 09:43:14', '2025-12-24 09:43:27'),
(8, 'TXN17668455925683', '', '', 1000.00, NULL, '../uploads/screenshots/donation_1766845592_1091.webp', 'pending', '', NULL, '2025-12-27 14:26:32', NULL),
(9, 'TXN17670800719319', '', '', 1000.00, NULL, '../uploads/screenshots/donation_1767080071_9443.jpg', 'pending', '', NULL, '2025-12-30 07:34:31', NULL),
(10, 'TXN17671796795870', '', '', 20000.00, NULL, '../uploads/screenshots/donation_1767179679_4823.png', 'verified', '', NULL, '2025-12-31 11:14:39', '2025-12-31 11:16:21');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_images`
--

CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` varchar(100) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `title_np` varchar(200) DEFAULT NULL,
  `description_np` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery_images`
--

INSERT INTO `gallery_images` (`id`, `image_url`, `category`, `title`, `description`, `uploaded_by`, `uploaded_at`, `is_active`, `title_np`, `description_np`) VALUES
(3, '1767002941_6952533dba4ae_vote.jpg', 'speeches', 'interview of ramesh prasad', 'interviews', NULL, '2025-12-22 17:42:32', 1, 'रमेश प्रसाद अधिकारी', 'रमेश प्रसाद अधिकारी'),
(4, '1767002990_6952536e488a0_vote.jpg', 'speeches', 'Ramesh vote appeal', 'vote appeal', NULL, '2025-12-22 18:12:39', 1, 'रमेश प्रसाद अधिकारी', 'रमेश प्रसाद अधिकारी'),
(5, '1767001718_69524e766d2a0_unnamed.jpg', 'speeches', 'commitment', 'Adhikari is known for his strong commitment to social justice and inclusive development. He has played an important role in mobilizing local communities, promoting education, and encouraging active civic participation for sustainable national progress.', NULL, '2025-12-23 09:53:35', 1, 'परिचित', 'अधिकारी सामाजिक न्याय र समावेशी विकासप्रतिको दृढ प्रतिबद्धताका लागि परिचित छन्। उनले स्थानीय समुदायलाई सक्रिय बनाउने, शिक्षाको प्रवर्द्धन गर्ने र दिगो राष्ट्रिय प्रगतिका लागि नागरिक सहभागिता बढाउने कार्यमा महत्वपूर्ण भूमिका खेलेका छन्।'),
(6, '1767001665_69524e4159279_unnamed2.jpg', 'public', 'press confirence', 'He has served in various leadership roles within local government and non-government organizations, focusing on education, rural development, and youth empowerment.', NULL, '2025-12-23 16:55:06', 1, 'सामुदायिक', 'उनले स्थानीय सरकार तथा गैर–सरकारी संस्थाहरूमा विभिन्न नेतृत्वदायी भूमिकामा सेवा प्रदान गरेका छन्, जहाँ उनले शिक्षा, ग्रामीण विकास र युवा सशक्तीकरणमा विशेष ध्यान दिएका छन्।'),
(7, '1767003003_6952537bd1bf8_bg.jpg', 'meetings', 'parliment speeches', 'first parliment speeches', NULL, '2025-12-26 07:33:53', 1, 'सामुदायिक', 'थनजवनद');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','textarea','image','url','email','phone','number','json') DEFAULT 'text',
  `setting_category` varchar(50) DEFAULT 'general',
  `setting_group` varchar(50) DEFAULT 'basic',
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `setting_category`, `setting_group`, `description`, `updated_at`) VALUES
(1, 'hero_bg_image', 'uploads/settings/1767001313_69524ce154866_bg.jpg', 'image', 'hero', 'hero_section', 'Hero section background image URL', '2025-12-29 09:41:53'),
(2, 'hero_main_image', 'uploads/settings/1767002434_69525142e1197_unnamed4.jpg', 'image', 'hero', 'hero_section', 'Main politician photo URL', '2025-12-29 10:00:34'),
(3, 'hero_party_logo', 'uploads/settings/1767004130_695257e2d9b29_OIP.webp', 'image', 'hero', 'hero_section', 'Political party logo/flag', '2025-12-29 10:28:50'),
(4, 'hero_party_name_en', 'RA.SA.PA', 'text', 'hero', 'hero_section', 'Party name in English', '2025-12-31 10:11:28'),
(5, 'hero_party_name_np', 'नेकपा (एमाले)', 'text', 'hero', 'hero_section', 'Party name in Nepali', '2025-12-31 10:11:28'),
(6, 'hero_politician_name_en', 'Ramesh Prasad Adhikari', 'text', 'hero', 'hero_section', 'Politician name in English', '2025-12-31 10:11:28'),
(7, 'hero_politician_name_np', 'रमेश प्रसाद अधिकारी', 'text', 'hero', 'hero_section', 'Politician name in Nepali', '2025-12-31 10:11:28'),
(8, 'hero_politician_title_en', 'Former Prime Minister of Nepal · Chairman of RA.SA.PA', 'text', 'hero', 'hero_section', 'Politician title in English', '2025-12-31 10:11:28'),
(9, 'hero_politician_title_np', 'नेपालका पूर्व प्रधानमन्त्री · नेकपा (एमाले) का अध्यक्ष', 'text', 'hero', 'hero_section', 'Politician title in Nepali', '2025-12-31 10:11:28'),
(10, 'hero_years_in_politics', '42', 'number', 'hero', 'hero_section', 'Years in politics stat', '2025-12-31 10:11:28'),
(11, 'hero_terms_as_pm', '4', 'number', 'hero', 'hero_section', 'Terms as PM stat', '2025-12-31 10:11:28'),
(12, 'hero_approval_rating', '75', 'number', 'hero', 'hero_section', 'Approval rating stat', '2025-12-31 10:11:28'),
(13, 'about_title_en', 'About', 'text', 'about', 'about_section', 'About section title in English', '2025-12-31 10:11:28'),
(14, 'about_title_np', 'बारेमा', 'text', 'about', 'about_section', 'About section title in Nepali', '2025-12-31 10:11:28'),
(15, 'about_subtitle_en', 'A visionary leader dedicated to Nepal\'s development and prosperity', 'text', 'about', 'about_section', 'About subtitle in English', '2025-12-31 10:11:28'),
(16, 'about_subtitle_np', 'नेपालको विकास र समृद्धिका लागि समर्पित दूरदर्शी नेता', 'text', 'about', 'about_section', 'About subtitle in Nepali', '2025-12-31 10:11:28'),
(17, 'about_content_en', 'Ramesh Prasad Adhikari is a dedicated Nepalese social worker and community leader. Born on July 15, 1978, in Lamjung District, Nepal, Adhikari has spent more than two decades working for social development and public welfare.\r\n\r\nHe has served in various leadership roles within local government and non-government organizations, focusing on education, rural development, and youth empowerment. His contributions have helped improve access to basic services in remote communities.\r\n\r\nAdhikari is known for his strong commitment to social justice and inclusive development. He has played an important role in mobilizing local communities, promoting education, and encouraging active civic participation for sustainable national progress.', 'textarea', 'about', 'about_section', 'About content in English', '2025-12-31 10:11:28'),
(18, 'about_content_np', 'रमेश प्रसाद अधिकारी एक समर्पित नेपाली समाजसेवी तथा सामुदायिक नेता हुन्। उनी सन् १९७८ जुलाई १५ मा नेपालको लमजुङ जिल्लामा जन्मिएका हुन्। अधिकारीले दुई दशकभन्दा बढी समय सामाजिक विकास र सार्वजनिक कल्याणको क्षेत्रमा काम गर्दै आएका छन्।\r\n\r\nउनले स्थानीय सरकार तथा गैर–सरकारी संस्थाहरूमा विभिन्न नेतृत्वदायी भूमिकामा सेवा प्रदान गरेका छन्, जहाँ उनले शिक्षा, ग्रामीण विकास र युवा सशक्तीकरणमा विशेष ध्यान दिएका छन्। उनका योगदानहरूले दुर्गम समुदायहरूमा आधारभूत सेवामा पहुँच सुधार गर्न मद्दत पुर्‍याएको छ।\r\n\r\nअधिकारी सामाजिक न्याय र समावेशी विकासप्रतिको दृढ प्रतिबद्धताका लागि परिचित छन्। उनले स्थानीय समुदायलाई सक्रिय बनाउने, शिक्षाको प्रवर्द्धन गर्ने र दिगो राष्ट्रिय प्रगतिका लागि नागरिक सहभागिता बढाउने कार्यमा महत्वपूर्ण भूमिका खेलेका छन्।', 'textarea', 'about', 'about_section', 'About content in Nepali', '2025-12-31 10:11:28'),
(19, 'about_birth_date_en', 'February 22, 1952', 'text', 'about', 'quick_facts', 'Birth date in English', '2025-12-31 10:11:28'),
(20, 'about_birth_date_np', '२००८ फागुन १०', 'text', 'about', 'quick_facts', 'Birth date in Nepali', '2025-12-31 10:11:28'),
(21, 'about_education_en', 'Tribhuvan University', 'text', 'about', 'quick_facts', 'Education in English', '2025-12-31 10:11:28'),
(22, 'about_education_np', 'त्रिभुवन विश्वविद्यालय', 'text', 'about', 'quick_facts', 'Education in Nepali', '2025-12-31 10:11:28'),
(23, 'about_constituency_en', 'Jhapa-5', 'text', 'about', 'quick_facts', 'Constituency in English', '2025-12-31 10:11:28'),
(24, 'about_constituency_np', 'झापा-५', 'text', 'about', 'quick_facts', 'Constituency in Nepali', '2025-12-31 10:11:28'),
(25, 'about_career_start_en', 'Since 1970', 'text', 'about', 'quick_facts', 'Political career start in English', '2025-12-31 10:11:28'),
(26, 'about_career_start_np', 'सन् १९७० देखि', 'text', 'about', 'quick_facts', 'Political career start in Nepali', '2025-12-31 10:11:28'),
(27, 'footer_party_logo', 'uploads/settings/1767165360_6954cdb04d386_raswopa1669525659.jpg', 'image', 'footer', 'footer_section', 'Footer party logo', '2025-12-31 07:16:00'),
(28, 'footer_politician_name_en', 'RP Adhikhari', 'text', 'footer', 'footer_section', 'Footer politician name in English', '2025-12-31 10:11:28'),
(29, 'footer_politician_name_np', 'र.प्र अधिकारी', 'text', 'footer', 'footer_section', 'Footer politician name in Nepali', '2025-12-31 10:11:28'),
(30, 'footer_description_en', 'Official portfolio of Ramesh Prasad Adhikari, former Prime Minister of Nepal and Chairman of CPN (UML).', 'textarea', 'footer', 'footer_section', 'Footer description in English', '2025-12-31 10:11:28'),
(31, 'footer_description_np', 'रमेश प्रसाद अधिकारी  नेपालका पूर्व प्रधानमन्त्री र नेकपा (एमाले) का अध्यक्षको आधिकारिक पोर्टफोलियो।', 'textarea', 'footer', 'footer_section', 'Footer description in Nepali', '2025-12-31 10:11:28'),
(32, 'footer_address_en', 'Kathmandu, Nepal', 'text', 'footer', 'contact_info', 'Address in English', '2025-12-31 10:11:28'),
(33, 'footer_address_np', 'काठमाडौं, नेपाल', 'text', 'footer', 'contact_info', 'Address in Nepali', '2025-12-31 10:11:28'),
(34, 'footer_phone', '+977-9869432784', 'phone', 'footer', 'contact_info', 'Contact phone number', '2025-12-31 10:11:28'),
(35, 'footer_email', 'Rameshprasad@gmail.com', 'email', 'footer', 'contact_info', 'Contact email', '2025-12-31 10:11:28'),
(36, 'footer_copyright_en', '© 2026 Ramesh Prasad Adhikari Portfolio. All rights reserved.', 'text', 'footer', 'copyright', 'Copyright text in English', '2025-12-31 10:11:28'),
(37, 'footer_copyright_np', '© २०२६ रमेश प्रसाद अधिकारी  पोर्टफोलियो। सर्वाधिकार सुरक्षित।', 'text', 'footer', 'copyright', 'Copyright text in Nepali', '2025-12-31 10:11:28'),
(38, 'social_facebook', 'https://www.facebook.com/UMLprezKPSharmaOli/', 'url', 'footer', 'social_media', 'Facebook page URL', '2025-12-31 10:11:28'),
(39, 'social_twitter', 'https://x.com/kpsharmaoli', 'url', 'footer', 'social_media', 'Twitter/X profile URL', '2025-12-31 10:11:28'),
(40, 'social_instagram', 'https://www.instagram.com/kpsharmaoli/', 'url', 'footer', 'social_media', 'Instagram profile URL', '2025-12-31 10:11:28'),
(41, 'social_youtube', 'https://www.youtube.com/@kpsharmaoli', 'url', 'footer', 'social_media', 'YouTube channel URL', '2025-12-31 10:11:28'),
(42, 'secretary_name_en', 'Rajesh Sharma', 'text', 'contact', 'secretary', 'Secretary name in English', '2025-12-31 10:11:28'),
(43, 'secretary_name_np', 'राजेश शर्मा', 'text', 'footer', 'secretary', 'Secretary name in Nepali', '2025-12-31 10:11:28'),
(44, 'secretary_title_en', 'Personal Secretary', 'text', 'contact', 'secretary', 'Secretary title in English', '2025-12-31 10:11:28'),
(45, 'secretary_title_np', 'व्यक्तिगत सचिव', 'text', 'contact', 'secretary', 'Secretary title in Nepali', '2025-12-31 10:11:28'),
(46, 'secretary_phone', '+977-9864499368', 'phone', 'contact', 'secretary', 'Secretary phone number', '2025-12-31 10:11:28'),
(47, 'secretary_email', 'rajeshsharma@gmail.com', 'email', 'contact', 'secretary', 'Secretary email', '2025-12-31 10:11:28'),
(48, 'secretary_photo', 'uploads/settings/1767165219_6954cd2368c77_secretery.jpg', 'image', 'contact', 'secretary', 'Secretary photo URL', '2025-12-31 07:13:39'),
(49, 'donation_qr_code', 'uploads/settings/1766986586_6952135ac1b39_Screenshot_2025-12-17-12-19-45-501_com.f1soft.nepalmobilebanking.jpg', 'image', 'donation', 'donation_section', 'Donation QR code image', '2025-12-29 05:36:26'),
(50, 'donation_default_amount', '1000', 'number', 'donation', 'donation_section', 'Default donation amount', '2025-12-31 10:11:28'),
(51, 'donation_min_amount', '100', 'number', 'donation', 'donation_section', 'Minimum donation amount', '2025-12-31 10:11:28'),
(52, 'donation_bank_name_en', 'Nepal Investment Mega Bank', 'text', 'donation', 'donation_section', 'Bank name in English', '2025-12-31 10:11:28'),
(53, 'donation_bank_name_np', 'नेपाल ईन्भेस्टमेन्ट मेगा बैंक', 'text', 'donation', 'donation_section', 'Bank name in Nepali', '2025-12-31 10:11:28'),
(54, 'donation_account_number', '1234567890123456', 'text', 'donation', 'donation_section', 'Bank account number', '2025-12-31 10:11:28'),
(55, 'donation_account_name', 'KP Sharma Oli Donation Account', 'text', 'donation', 'donation_section', 'Account holder name', '2025-12-31 10:11:28'),
(56, 'site_title', 'Ramesh Prasad Adhikari - Official Portfolio | ⭐ LeaderX', 'text', 'general', 'site_info', 'Website title', '2025-12-31 10:11:28'),
(57, 'site_description', 'Official portfolio of KP Sharma Oli - Former Prime Minister of Nepal, Chairman of CPN (UML)', 'textarea', 'general', 'site_info', 'Website meta description', '2025-12-31 10:11:28'),
(58, 'site_keywords', 'KP Sharma Oli, Nepal Prime Minister, CPN UML, Nepali politician, political portfolio', 'text', 'general', 'site_info', 'Website meta keywords', '2025-12-31 10:11:28'),
(59, 'site_author', 'NetaKnown', 'text', 'general', 'site_info', 'Website author', '2025-12-31 10:11:28'),
(60, 'site_language', 'en', 'text', 'general', 'site_info', 'Supported languages', '2025-12-28 05:19:02'),
(3779, 'gallery_categories_en', 'speeches, meetings, public events', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3780, 'gallery_categories_np', 'भाषणहरू, बैठकहरू, सार्वजनिक कार्यक्रम', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3781, 'gallery_items_per_page', '12', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3782, 'gallery_order', 'latest', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3783, 'gallery_thumbnail_width', '300', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3784, 'gallery_thumbnail_height', '200', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3785, 'timeline_title_en', 'Career Timeline', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3786, 'timeline_subtitle_en', 'Four decades of political journey and achievements', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3787, 'timeline_title_np', 'कार्यकाल समयरेखा', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3788, 'timeline_subtitle_np', 'चार दशकको राजनीतिक यात्रा र उपलब्धिहरू', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3789, 'timeline_items_visible', '5', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3790, 'timeline_orientation', 'horizontal', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3791, 'timeline_color_scheme', 'danger', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3792, 'activities_title_en', 'Recent Activities', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3793, 'activities_subtitle_en', 'Latest engagements and public activities', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3794, 'activities_title_np', 'हालका गतिविधिहरू', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3795, 'activities_subtitle_np', 'नवीनतम संलग्नताहरू र सार्वजनिक गतिविधिहरू', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3796, 'activities_status_en', 'upcoming, completed, cancelled', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3797, 'activities_status_np', 'आगामी, सम्पन्न, रद्द', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3798, 'activities_per_page', '6', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3799, 'achievements_title_en', 'Major Achievements', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3800, 'achievements_subtitle_en', 'Significant contributions to Nepal\'s development', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3801, 'achievements_title_np', 'प्रमुख उपलब्धिहरू', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3802, 'achievements_subtitle_np', 'नेपालको विकासमा महत्वपूर्ण योगदान', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3803, 'achievements_categories_en', 'Political, Social, Economic, Development', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3804, 'achievements_categories_np', 'राजनीतिक, सामाजिक, आर्थिक, विकास', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3805, 'achievements_per_row', '3', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3806, 'achievements_order', 'importance', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3807, 'videos_title_en', 'Video Showcase', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3808, 'videos_subtitle_en', 'Important speeches and interviews', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3809, 'videos_title_np', 'भिडियो प्रदर्शनी', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3810, 'videos_subtitle_np', 'महत्वपूर्ण भाषणहरू र अन्तर्वार्ताहरू', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3811, 'video_autoplay', '0', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3812, 'video_controls', '1', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3813, 'video_loop', '0', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3814, 'videos_per_row', '3', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3815, 'contact_title_en', 'Contact', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3816, 'contact_subtitle_en', 'Get in touch for meetings, inquiries, or support', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3817, 'contact_title_np', 'सम्पर्क', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3818, 'contact_subtitle_np', 'बैठकहरू, जानकारी, वा सहयोगको लागि सम्पर्क गर्नुहोस्', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3823, 'contact_form_active', '1', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3824, 'appointment_form_active', '1', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3825, 'contact_notification_email', 'notifications@example.com', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3832, 'donation_account_type', 'Savings Account', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3848, 'maintenance_mode', '0', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3849, 'contact_email', 'admin@example.com', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3850, 'timezone', 'Asia/Kathmandu', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3851, 'cache_enabled', '1', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3852, 'cache_duration', '3600', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3853, 'analytics_enabled', '1', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3860, 'secretary_office', 'Prime Minister Office', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3861, 'secretary_office_address', 'Singha Durbar, Kathmandu', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3862, 'secretary_working_hours', '10:00 AM - 5:00 PM', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(3863, 'secretary_appointment_days', 'Sunday - Friday', 'text', 'general', 'basic', NULL, '2025-12-28 05:19:02'),
(7181, 'more_videos_link', 'https://youtu.be/cQb4UxF7DL8?si=6Y4A5MLrfF9MI-3L', 'url', 'videos', 'more_videos', 'URL for More Videos button', '2025-12-31 10:11:28'),
(7182, 'more_videos_button_text_en', 'More Video Link', 'text', 'videos', 'more_videos', 'Button Text in English', '2025-12-31 10:11:28'),
(7183, 'more_videos_button_text_np', 'थप भिडियो लिङ्कहरू', 'text', 'videos', 'more_videos', 'Button Text in Nepali', '2025-12-31 10:11:28');

-- --------------------------------------------------------

--
-- Table structure for table `timeline_entries`
--

CREATE TABLE `timeline_entries` (
  `id` int(11) NOT NULL,
  `year` varchar(20) NOT NULL,
  `title_en` varchar(255) NOT NULL,
  `title_np` varchar(255) DEFAULT NULL,
  `content_en` text NOT NULL,
  `content_np` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timeline_entries`
--

INSERT INTO `timeline_entries` (`id`, `year`, `title_en`, `title_np`, `content_en`, `content_np`, `created_at`) VALUES
(7, '1991', 'Early Political Involvemen', 'प्रारम्भिक राजनीतिक संलग्नता', 'Joined the Communist Party of Nepal and became actively involved in student politics...', 'नेपाल कम्युनिस्ट पार्टीमा सामेल भए र पञ्चायत प्रणाली विरुद्ध विद्यार्थी राजनीति र भूमिगत गतिविधिहरूमा सक्रिय रूपमा संलग्न भए।', '2025-12-29 09:34:02'),
(8, '2005', 'Early Social Involvement', 'प्रारम्भिक सामाजिक संलग्नता', 'Ramesh Prasad Adhikari began his journey in social service by actively participating in local community initiatives.', 'रमेश प्रसाद अधिकारीले स्थानीय समुदायका पहलहरूमा सक्रिय सहभागिता जनाउँदै सामाजिक सेवाको यात्रा सुरु गर्नुभयो।', '2025-12-29 17:37:19'),
(9, '2010', 'Leadership in Community Development', 'सामुदायिक विकासमा नेतृत्व', 'He took leadership roles in several community development programs focusing on education and health.', 'शिक्षा र स्वास्थ्यमा केन्द्रित विभिन्न सामुदायिक विकास कार्यक्रमहरूमा उहाँले नेतृत्वदायी भूमिका निर्वाह गर्नुभयो।', '2025-12-29 17:37:19'),
(10, '2015', 'District-Level Representation', 'जिल्ला तहको प्रतिनिधित्व', 'Ramesh Prasad Adhikari represented his district in regional meetings and policy discussions.', 'क्षेत्रीय बैठक र नीति छलफलहरूमा रमेश प्रसाद अधिकारीले आफ्नो जिल्लाको प्रतिनिधित्व गर्नुभयो।', '2025-12-29 17:37:19'),
(11, '2019', 'Participation in National Conferences', 'राष्ट्रिय सम्मेलनमा सहभागिता', 'He actively participated in national-level conferences related to governance and development.', 'शासन र विकाससम्बन्धी राष्ट्रिय स्तरका सम्मेलनहरूमा उहाँ सक्रिय रूपमा सहभागी हुनुभयो।', '2025-12-29 17:37:19'),
(12, '2023', 'Senior Advisory Role', 'वरिष्ठ सल्लाहकारको भूमिका', 'Ramesh Prasad Adhikari was appointed to a senior advisory role focusing on governance and social development.', 'शासन र सामाजिक विकासमा केन्द्रित वरिष्ठ सल्लाहकारको भूमिकामा रमेश प्रसाद अधिकारी नियुक्त हुनुभयो।', '2025-12-29 17:37:19');

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `title_np` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_np` text DEFAULT NULL,
  `video_url` varchar(255) NOT NULL,
  `youtube_url` varchar(500) DEFAULT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `category` enum('speeches','interviews','campaigns','press') DEFAULT 'speeches',
  `language` enum('en','np') DEFAULT 'en',
  `duration` int(11) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`id`, `title`, `title_np`, `description`, `description_np`, `video_url`, `youtube_url`, `thumbnail_url`, `category`, `language`, `duration`, `views`, `uploaded_at`) VALUES
(3, 'Parliament Address - Constitutional Reforms', 'संसदीय सम्बोधन - संवैधानिक सुधार', 'Historic speech in the Parliament of Nepal about constitutional reforms and national development. KP Sharma Oli addresses the nation on the importance of the new constitution.', 'संवैधानिक सुधार र राष्ट्रिय विकासको बारेमा नेपालको संसदमा ऐतिहासिक भाषण। केपी शर्मा ओलीले नयाँ संविधानको महत्वमा राष्ट्रलाई सम्बोधन गरे।/', 'KP Sharma Oli Speech Parliament of Nepal 27 Sept 2016.mp4', 'ArODQ82g-qU', '1767160334_6954ba0ec4c58_inter.jpg', 'interviews', 'en', NULL, 0, '2025-12-26 10:30:04'),
(4, 'Exclusive Political Interview', 'विशेष राजनीतिक अन्तर्वार्ता', 'In-depth interview discussing current political scenarios, party strategies, and future plans for Nepal development.', 'वर्तमान राजनीतिक परिदृश्य, पार्टी रणनीतिहरू र नेपाल विकासको भविष्यका योजनाहरूको छलफल गर्दै गहिरो अन्तर्वार्ता।', 'Rajesh latest speech chitwan.mp4', 'U0lWpIOC4ck', '1767115945_69540ca932f67_inter.jpg', 'interviews', 'en', NULL, 0, '2025-12-26 10:30:04'),
(5, 'Election Campaign Rally 2022', 'चुनाव अभियान र्याली २०२२', 'Addressing thousands of supporters during the election campaign rally. Discussing development agenda and party manifesto.', 'चुनावी अभियान र्यालीमा हजारौं समर्थकहरूलाई सम्बोधन गर्दै। विकास एजेन्डा र पार्टी घोषणापत्रको छलफल।', 'KP OLI VOTE APPEAL _ NATIONAL ELECTION _ VOTE FOR SUN _ KATHA NEPAL.mp4', 'x9qDau3l1rM', '1767002761_6952528923292_vote.jpg', 'campaigns', 'en', NULL, 0, '2025-12-26 10:30:04'),
(6, 'Press Conference on Foreign Policy', 'विदेश नीति विषयक प्रेस कान्फ्रेन्स', 'Addressing national and international media about Nepal foreign policy, international relations, and diplomatic achievements.', 'नेपालको विदेश नीति, अन्तर्राष्ट्रिय सम्बन्ध र कूटनीतिक उपलब्धिहरूको बारेमा राष्ट्रिय र अन्तर्राष्ट्रिय मिडियालाई सम्बोधन।', 'kpoli_press.mp4', 'IU4Ni0ZaLIQ', '1767002792_695252a8b7adf_sp.jpg', 'press', 'en', NULL, 0, '2025-12-26 10:30:04'),
(7, 'Economic Development Speech', 'आर्थिक विकास भाषण', 'Detailed speech about economic policies, infrastructure development, and plans for making Nepal a prosperous nation.', 'आर्थिक नीतिहरू, पूर्वाधार विकास र नेपाललाई समृद्ध राष्ट्र बनाउने योजनाहरूको बारेमा विस्तृत भाषण।', '', 'xyz12345678', '1767002668_6952522cbbbf2_sp.jpg', 'speeches', 'en', NULL, 0, '2025-12-26 10:30:04');

-- --------------------------------------------------------

--
-- Table structure for table `video_links`
--

CREATE TABLE `video_links` (
  `id` int(10) UNSIGNED NOT NULL,
  `title_en` varchar(255) NOT NULL,
  `title_np` varchar(255) DEFAULT '',
  `url` text NOT NULL,
  `icon` varchar(255) DEFAULT 'fas fa-video',
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date` (`activity_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_priority` (`priority`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_appointments_status` (`status`),
  ADD KEY `idx_appointments_date` (`preferred_date`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_donations_status` (`status`),
  ADD KEY `idx_donations_created` (`created_at`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `timeline_entries`
--
ALTER TABLE `timeline_entries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `video_links`
--
ALTER TABLE `video_links`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7296;

--
-- AUTO_INCREMENT for table `timeline_entries`
--
ALTER TABLE `timeline_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `video_links`
--
ALTER TABLE `video_links`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
