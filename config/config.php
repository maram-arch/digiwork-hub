<?php
/**
 * DigiWork Hub — Configuration unifiée
 * Fournit :
 *   - $pdo  : connexion PDO globale (compatibilité modules collègues)
 *   - Config::getConnexion() : méthode statique (compatibilité socle auth)
 *   - Migrations automatiques au démarrage
 */

// ── Paramètres de connexion ──────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'digiwork-hub');
define('DB_USER', 'root');
define('DB_PASS', '');

// ── Classe Config (socle auth + migrations) ──────────────────────────────────
class Config
{
    private static ?PDO $pdo = null;
    private static bool $schemaEnsured = false;

    public static function getConnexion(): PDO
    {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
                self::runMigrations(self::$pdo);
            } catch (PDOException $e) {
                throw new RuntimeException('Connexion base de données impossible : ' . $e->getMessage(), 0, $e);
            }
        }

        if (!(self::$pdo instanceof PDO)) {
            throw new RuntimeException('Connexion base de données indisponible.');
        }

        return self::$pdo;
    }

    // ── Migrations ────────────────────────────────────────────────────────────

    private static function runMigrations(PDO $pdo): void
    {
        if (self::$schemaEnsured) {
            return;
        }
        self::$schemaEnsured = true;

        self::ensureUserColumns($pdo);
        self::ensureOtpSchema($pdo);
        self::ensureInscriptionColumns($pdo);
        self::ensureMailTable($pdo);
        self::ensureOfferSeeds($pdo);
        self::ensureHistoriqueTable($pdo);
    }

    private static function ensureUserColumns(PDO $pdo): void
    {
        try {
            // mdp doit être varchar(255) pour bcrypt
            $stmt = $pdo->query("SHOW COLUMNS FROM `user` LIKE 'mdp'");
            $col  = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
            if ($col && !str_contains(strtolower((string)($col['Type'] ?? '')), 'varchar(255)')) {
                $pdo->exec("ALTER TABLE `user` MODIFY `mdp` VARCHAR(255) NOT NULL");
            }

            // is_online
            $stmt = $pdo->query("SHOW COLUMNS FROM `user` LIKE 'is_online'");
            if (!($stmt && $stmt->fetch())) {
                $pdo->exec("ALTER TABLE `user` ADD COLUMN `is_online` TINYINT(1) NOT NULL DEFAULT 0 AFTER `tel`");
            }

            // last_activity
            $stmt = $pdo->query("SHOW COLUMNS FROM `user` LIKE 'last_activity'");
            if (!($stmt && $stmt->fetch())) {
                $pdo->exec("ALTER TABLE `user` ADD COLUMN `last_activity` DATETIME NULL DEFAULT NULL AFTER `is_online`");
            }

            // is_verified
            $stmt = $pdo->query("SHOW COLUMNS FROM `user` LIKE 'is_verified'");
            if (!($stmt && $stmt->fetch())) {
                $pdo->exec("ALTER TABLE `user` ADD COLUMN `is_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `tel`");
            }
        } catch (Throwable $e) {
            // Ignore — continue with existing schema
        }
    }

    public static function ensureOtpSchema(PDO $pdo): void
    {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `otp_verification` (
                `id`         INT(11)      NOT NULL AUTO_INCREMENT,
                `id_user`    INT(11)      NOT NULL,
                `otp_hash`   VARCHAR(255) NOT NULL,
                `expires_at` DATETIME     NOT NULL,
                `used`       TINYINT(1)   NOT NULL DEFAULT 0,
                `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_otp_user` (`id_user`),
                CONSTRAINT `fk_otp_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            // context column
            $stmt = $pdo->query("SHOW COLUMNS FROM `otp_verification` LIKE 'context'");
            if (!($stmt && $stmt->fetch())) {
                $pdo->exec("ALTER TABLE `otp_verification` ADD COLUMN `context` VARCHAR(20) NOT NULL DEFAULT 'signup' AFTER `used`");
                try {
                    $pdo->exec("ALTER TABLE `otp_verification` ADD KEY `idx_otp_user_context` (`id_user`, `context`)");
                } catch (Throwable $e) { /* index may already exist */ }
            }
        } catch (Throwable $e) {
            // Ignore
        }
    }

    private static function ensureInscriptionColumns(PDO $pdo): void
    {
        try {
            // nom
            $stmt = $pdo->query("SHOW COLUMNS FROM `inscription` LIKE 'nom'");
            if (!($stmt && $stmt->fetch())) {
                $pdo->exec("ALTER TABLE `inscription` ADD COLUMN `nom` VARCHAR(100) DEFAULT NULL AFTER `id_inscription`");
            }
            // post
            $stmt = $pdo->query("SHOW COLUMNS FROM `inscription` LIKE 'post'");
            if (!($stmt && $stmt->fetch())) {
                $pdo->exec("ALTER TABLE `inscription` ADD COLUMN `post` VARCHAR(50) DEFAULT NULL AFTER `nom`");
            }
            // nber_invi
            $stmt = $pdo->query("SHOW COLUMNS FROM `inscription` LIKE 'nber_invi'");
            if (!($stmt && $stmt->fetch())) {
                $pdo->exec("ALTER TABLE `inscription` ADD COLUMN `nber_invi` INT(11) NOT NULL DEFAULT 0 AFTER `post`");
            }
        } catch (Throwable $e) {
            // Table may not exist yet — ignore
        }
    }

    private static function ensureMailTable(PDO $pdo): void
    {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `mail` (
                `id`         INT(11)      NOT NULL AUTO_INCREMENT,
                `email`      VARCHAR(120) NOT NULL,
                `id event`   INT(11)      DEFAULT NULL,
                `sujet`      VARCHAR(255) NOT NULL,
                `text`       TEXT         NOT NULL,
                `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Throwable $e) {
            // Ignore
        }
    }

    private static function ensureOfferSeeds(PDO $pdo): void
    {
        try {
            // Check if offer table is empty
            $count = (int) $pdo->query("SELECT COUNT(*) FROM `offer`")->fetchColumn();
            if ($count > 0) return;

            // Insert entreprise seeds first (offer has FK to entreprise)
            $pdo->exec("INSERT IGNORE INTO `entreprise` (`id-entr`, `id_user`, `nom-ent`, `adress`, `discreption`) VALUES
                (1, 1, 'DigiWork Hub',   'Tunis, Tunisie',  'Plateforme intelligente pour entrepreneurs digitaux'),
                (2, 1, 'TechStart SARL', 'Sousse, Tunisie', 'Startup spécialisée en développement web et mobile'),
                (3, 1, 'InnovateTN',     'Sfax, Tunisie',   'Agence digitale et conseil en transformation numérique')");

            // Insert offer seeds
            $pdo->exec("INSERT IGNORE INTO `offer` (`id-offer`, `titre`, `discription`, `competence`, `date-limiter`, `adresse`, `type`, `id-enter`) VALUES
                (1, 'Dev Web React',   'Développement application web React pour startup fintech',   'React, JS, CSS',        '2026-12-31', 'Tunis',  'CDI',       1),
                (2, 'Dev Mobile',      'Création application mobile cross-platform Flutter',         'Flutter, Dart',         '2026-11-30', 'Sousse', 'Freelance', 2),
                (3, 'Designer UI/UX',  'Conception interfaces utilisateur pour plateforme SaaS',     'Figma, Adobe XD',       '2026-10-31', 'Sfax',   'CDD',       3),
                (4, 'Dev Backend PHP', 'Développement API REST PHP pour système de gestion',         'PHP, MySQL, REST',      '2026-12-15', 'Tunis',  'CDI',       1),
                (5, 'Data Analyst',    'Analyse de données et création de tableaux de bord BI',      'Python, SQL, Power BI', '2026-11-15', 'Tunis',  'Freelance', 2)");
        } catch (Throwable $e) {
            // Ignore — table may not exist yet or FK constraint issue
        }
    }

    private static function ensureHistoriqueTable(PDO $pdo): void
    {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `historique` (
                `id_historique` INT(11)      NOT NULL AUTO_INCREMENT,
                `action`        VARCHAR(50)  NOT NULL,
                `entite`        VARCHAR(50)  NOT NULL,
                `description`   TEXT         NOT NULL,
                `date_action`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_historique`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Throwable $e) {
            // Ignore
        }
    }
}

// ── $pdo global (compatibilité modules collègues) ────────────────────────────
try {
    $pdo = Config::getConnexion();
} catch (Throwable $e) {
    // $pdo reste null — les modules qui en ont besoin afficheront leur propre erreur
    $pdo = null;
}
