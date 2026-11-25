<?php
if (!defined('BPS_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}

/**
 * Sistema Multi-Idioma (i18n) para BASIC POINTS STATS Web
 * Idiomas soportados: Español (es), English (en), Português (pt / bp)
 */

if (!function_exists('bps_supported_languages')) {
    function bps_supported_languages() {
        return [
            'es' => 'ES · Español',
            'en' => 'EN · English',
            'pt' => 'PT · Português',
        ];
    }
}

if (!function_exists('bps_normalize_lang')) {
    function bps_normalize_lang($raw) {
        $code = strtolower(trim((string)$raw));
        if ($code === '') {
            return '';
        }
        if (strpos($code, 'bp') === 0 || strpos($code, 'pt') === 0) {
            return 'pt';
        }
        if (strpos($code, 'en') === 0) {
            return 'en';
        }
        if (strpos($code, 'es') === 0) {
            return 'es';
        }
        return '';
    }
}

if (!function_exists('bps_init_lang')) {
    function bps_init_lang() {
        static $resolved = null;
        if ($resolved !== null) {
            return $resolved;
        }

        $fromGet = isset($_GET['lang']) ? bps_normalize_lang($_GET['lang']) : '';
        if ($fromGet !== '') {
            $resolved = $fromGet;
            if (!headers_sent()) {
                $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off');
                if (PHP_VERSION_ID >= 70300) {
                    setcookie('bps_lang', $resolved, [
                        'expires'  => time() + 31536000,
                        'path'     => '/',
                        'secure'   => $isHttps,
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                } else {
                    setcookie('bps_lang', $resolved, time() + 31536000, '/; SameSite=Lax', '', $isHttps, true);
                }
            }
            return $resolved;
        }

        $fromCookie = isset($_COOKIE['bps_lang']) ? bps_normalize_lang($_COOKIE['bps_lang']) : '';
        if ($fromCookie !== '') {
            $resolved = $fromCookie;
            return $resolved;
        }

        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $parts = explode(',', (string)$_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($parts as $part) {
                $tag = trim(explode(';', $part)[0]);
                $norm = bps_normalize_lang($tag);
                if ($norm !== '') {
                    $resolved = $norm;
                    return $resolved;
                }
            }
        }

        $resolved = 'es';
        return $resolved;
    }
}

if (!function_exists('bps_lang')) {
    function bps_lang() {
        return bps_init_lang();
    }
}

if (!function_exists('bps_translations')) {
    function bps_translations() {
        static $dict = null;
        if ($dict !== null) {
            return $dict;
        }

        $dict = [
            'es' => [
                'site_subtitle'           => 'Panel Moderno de Estadísticas para Counter-Strike 1.6 • Compatible SteamID, Nombre & Multi-Server',
                'motd_subtitle'           => 'Counter-Strike 1.6 • Estadísticas Oficiales',
                'search_placeholder_hdr'  => 'Buscar por SteamID o Nombre...',
                'search_aria'             => 'Buscar jugador por SteamID o Nombre',
                'btn_search'              => 'Buscar',
                'btn_filter'              => 'Filtrar',
                'btn_apply'               => 'Aplicar',
                'btn_reset'               => 'Reset',
                'btn_clear'               => 'Limpiar',
                'btn_copy'                => 'Copiar',
                'btn_copy_steam'          => 'Copiar SteamID',
                'btn_view_profile'        => 'Ver Perfil',
                'btn_back_ranking'        => '← Volver al Ranking',
                'btn_search_other'        => 'Buscar otro jugador',
                'btn_all_weapons'         => '← Todas las Armas',
                'btn_go_search'           => 'Ir al Buscador',
                'discord_community'       => 'Comunidad Discord',
                'nav_home'                => 'Inicio',
                'nav_top'                 => 'TOP Ranking',
                'nav_ranking'             => 'Ranking',
                'nav_weapons'             => 'Armas',
                'nav_versus'              => 'Ranking Versus',
                'nav_versus_short'        => 'Versus',
                'nav_search'              => 'Buscar Jugador',
                'label_server'            => 'Servidor',
                'label_lang'              => 'Idioma',
                'all_servers'             => 'Todos los Servidores',
                'global_shared'           => 'Global Compartido',
                'global_short'            => 'Global',
                'per_server'              => 'Por Servidor',
                'toast_copied'            => 'SteamID copiado al portapapeles',
                'unnamed'                 => 'Sin Nombre',
                'no_steamid'              => 'Sin SteamID',
                'db_conn_error'           => 'No se pudo conectar a la base de datos.',
                'mode_disabled'           => 'Desactivado',
                'mode_name_12'            => 'Nombre (12)',
                'mode_steamid'            => 'SteamID',
                'mode_name'               => 'Nombre',
                'mode_lan_other'          => 'LAN / Otro',
                'badge_mode_name'         => 'Modo: Nombre',
                'badge_mode_steam'        => 'Modo: SteamID',

                // Tiers
                'tier_global'             => 'Global Elite',
                'tier_supreme'            => 'Supremo',
                'tier_eagle'              => 'Águila',
                'tier_master'             => 'Maestro',
                'tier_gold'               => 'Oro',
                'tier_silver'             => 'Plata',
                'tier_bronze'             => 'Bronce',
                'tier_recruit'            => 'Recluta',

                // Weapon categories & special names
                'wcat_pistols'            => 'Pistolas',
                'wcat_special'            => 'Armas Secundarias / Especiales',
                'wcat_smg'                => 'Escopetas y Subfusiles (SMG)',
                'wcat_rifles'             => 'Rifles de Asalto',
                'wcat_snipers'            => 'Francotiradores y Ametralladoras',
                'wname_knife'             => 'Cuchillo',
                'wname_hegrenade'         => 'Granada HE',
                'wname_c4'                => 'Bomba C4',

                // Home
                'home_hero_title'         => 'Estadísticas',
                'home_hero_desc'          => 'Consulta el rendimiento en tiempo real, busca cualquier jugador por SteamID o Nombre (máx. 12 letras) y explora armas, multikills y clutches en red Multi-Server.',
                'home_search_ph'          => 'Ej: STEAM_0:1:12345 o Nick...',
                'kpi_players'             => 'Jugadores Registrados',
                'kpi_players_sub'         => 'SteamID & Nombre',
                'kpi_kills'               => 'Bajas Totales',
                'kpi_hs'                  => 'Precisión Headshots',
                'kpi_hs_sub'              => '{n} tiros a la cabeza',
                'kpi_bombs'               => 'Bombas Plantadas',
                'kpi_bombs_sub'           => '{n} desarmadas',
                'kpi_aces_clutches'       => 'ACEs / Clutches',
                'kpi_aces_sub'            => 'Jugadas destacadas',
                'mvp_points'              => 'Líder (Skill Points)',
                'mvp_points_unit'         => 'Puntos',
                'mvp_kills'               => 'Máximo Asesino (Frags)',
                'mvp_kills_unit'          => 'Bajas',
                'mvp_ace'                 => 'Rey del ACE (5K)',
                'home_top5_title'         => 'TOP 5 Ranking General',
                'home_view_full_table'    => 'Ver tabla completa →',
                'home_no_players'         => 'Aún no hay jugadores registrados.',
                'home_deadliest_weapons'  => 'Armas Más Letales',
                'home_view_all_weapons'   => 'Ver todas las armas →',
                'home_no_weapon_kills'    => 'Aún no hay bajas registradas por arma.',
                'home_servers_title'      => 'Servidores Conectados (Multi-Server)',
                'home_servers_count'      => '{n} servidor(es)',
                'srv_online'              => 'En línea',
                'srv_registered'          => 'Registrado',
                'srv_save_prefix'         => 'Guardado:',
                'srv_last_pulse'          => 'Último pulso:',
                'srv_view_ranking'        => 'Ver Ranking →',

                // Tables & Top
                'col_player'              => 'Jugador',
                'col_player_steam'        => 'Jugador / SteamID',
                'col_name'                => 'Nombre',
                'col_id_type'             => 'Tipo ID',
                'col_rank_tier'           => 'Rango',
                'col_level'               => 'Nivel',
                'col_points'              => 'Puntos',
                'col_kills'               => 'Kills',
                'col_deaths'              => 'Deaths',
                'col_assists'             => 'Asist.',
                'col_hs_pct'              => '% HS',
                'col_c4_pd'               => 'C4 (P/D)',
                'col_wlt'                 => 'G / P / E',
                'col_wl'                  => 'G / P',
                'col_profile'             => 'Perfil',
                'col_share'               => 'Proporción',
                'col_weapon_kills'        => 'Bajas ({w})',
                'top_title'               => 'TOP Ranking General',
                'top_subtitle'            => 'Clasificación completa de jugadores ({n} encontrados). Haz clic en un jugador para ver su perfil completo.',
                'top_filter_ph'           => 'Filtrar por Nombre o SteamID...',
                'top_error'               => 'Error al recuperar la clasificación.',
                'top_empty'               => 'No hay jugadores registrados que coincidan con el filtro.',
                'motd_empty'              => 'No hay registros aún.',
                'motd_error'              => 'No se pudieron cargar los datos de la clasificación.',
                'sort_points'             => 'Orden: Puntos',
                'sort_kills'              => 'Orden: Kills',
                'sort_kd'                 => 'Orden: Ratio K/D',
                'sort_hs'                 => 'Orden: % Headshot',
                'sort_wins'               => 'Orden: Victorias',
                'sort_level'              => 'Orden: Nivel',

                // Search
                'search_title'            => 'Buscador de Jugadores',
                'search_subtitle'         => 'Encuentra estadísticas detalladas buscando por SteamID (ej. STEAM_0:1:...) o por Nombre (máx. 12 caracteres).',
                'search_input_ph'         => 'Escribe un SteamID (STEAM_0:...) o Nombre (máx. 12 letras)...',
                'search_mode_all'         => 'SteamID y Nombre',
                'search_mode_nick'        => 'Solo Nombre',
                'search_mode_authid'      => 'Solo SteamID',
                'search_error'            => 'No se pudo realizar la búsqueda en la base de datos.',
                'search_showing_query'    => 'Mostrando {shown} de {total} resultado(s) para',
                'search_showing_top'      => 'Mostrando jugadores destacados (escribe un Nombre o SteamID arriba para filtrar).',
                'search_empty'            => 'No se encontraron jugadores que coincidan con',

                // Player Profile
                'player_not_found'        => 'Jugador no encontrado',
                'player_not_found_desc'   => 'No pudimos localizar el perfil solicitado. Intenta buscar por SteamID o Nombre.',
                'player_of_total'         => 'de {n}',
                'player_last_server'      => 'Último servidor:',
                'player_last_seen'        => 'Última actividad:',
                'player_xp_next'          => '{pct} / 100 pts al Nivel {next}',
                'player_adr_label'        => 'ADR (Daño / Ronda)',
                'player_adr_sub'          => 'Daño total: {dmg} • RWS prom.: {rws}',
                'player_c4_label'         => 'Objetivos C4',
                'player_c4_sub'           => '{bp} plantadas • {bd} desarmadas',
                'player_matches_box'      => 'Rendimiento de Partidas y Rondas',
                'player_matches_played'   => 'Partidas Jugadas:',
                'player_winrate'          => 'Win Rate:',
                'player_wins'             => 'Victorias:',
                'player_ties'             => 'Empates:',
                'player_losses'           => 'Derrotas:',
                'player_rounds_total'     => 'Rondas Totales:',
                'player_effectiveness'    => 'Efectividad:',
                'player_rounds_won'       => 'Rondas Ganadas:',
                'player_rounds_lost'      => 'Rondas Perdidas:',
                'player_versus_box'       => 'Multikills & Clutches',
                'player_weapons_box'      => 'Estadísticas por Arma',
                'player_total_kills_sub'  => '{n} bajas totales',
                'player_no_weapons'       => 'Este jugador aún no registra bajas con armas.',

                // Versus
                'versus_title'            => 'Ranking Multikills & Clutches (Versus)',
                'versus_subtitle'         => 'Mejores jugadas individuales, bajas múltiples en una ronda y victorias en desventaja numérica (1 vs X).',
                'versus_ace_leader'       => 'Líder en Multikills 5K',
                'versus_clutch_leader'    => 'Especialista en 1 vs X',
                'versus_1v5_wins'         => '{n} victorias 1v5',
                'versus_filter_ph'        => 'Filtrar ranking Versus por Nombre o SteamID...',
                'versus_empty'            => 'No hay registros versus que coincidan.',
                'versus_error'            => 'Error al cargar las estadísticas Versus.',

                // Weapons
                'weapons_title'           => 'Estadísticas por Arma',
                'weapons_subtitle'        => 'Selecciona un arma para consultar la clasificación de sus mejores jugadores.',
                'weapons_quick_filter'    => 'Filtrar arma (ej: AK-47, AWP, Deagle)...',
                'weapons_kills_count'     => '{n} bajas',
                'weapon_invalid'          => 'Arma no válida.',
                'weapon_error'            => 'No se pudieron recuperar los datos de esta arma.',
                'weapon_total_kills'      => 'Bajas totales con esta arma:',
                'weapon_filter_ph'        => 'Filtrar jugadores en {w} por Nombre o SteamID...',
                'weapon_empty'            => 'No hay registros con esta arma aún.',
            ],

            'en' => [
                'site_subtitle'           => 'Modern Statistics Panel for Counter-Strike 1.6 • SteamID, Name & Multi-Server Ready',
                'motd_subtitle'           => 'Counter-Strike 1.6 • Official Statistics',
                'search_placeholder_hdr'  => 'Search by SteamID or Name...',
                'search_aria'             => 'Search player by SteamID or Name',
                'btn_search'              => 'Search',
                'btn_filter'              => 'Filter',
                'btn_apply'               => 'Apply',
                'btn_reset'               => 'Reset',
                'btn_clear'               => 'Clear',
                'btn_copy'                => 'Copy',
                'btn_copy_steam'          => 'Copy SteamID',
                'btn_view_profile'        => 'View Profile',
                'btn_back_ranking'        => '← Back to Ranking',
                'btn_search_other'        => 'Search another player',
                'btn_all_weapons'         => '← All Weapons',
                'btn_go_search'           => 'Go to Search',
                'discord_community'       => 'Discord Community',
                'nav_home'                => 'Home',
                'nav_top'                 => 'TOP Ranking',
                'nav_ranking'             => 'Ranking',
                'nav_weapons'             => 'Weapons',
                'nav_versus'              => 'Versus Ranking',
                'nav_versus_short'        => 'Versus',
                'nav_search'              => 'Find Player',
                'label_server'            => 'Server',
                'label_lang'              => 'Language',
                'all_servers'             => 'All Servers',
                'global_shared'           => 'Shared Global',
                'global_short'            => 'Global',
                'per_server'              => 'Per Server',
                'toast_copied'            => 'SteamID copied to clipboard',
                'unnamed'                 => 'Unnamed',
                'no_steamid'              => 'No SteamID',
                'db_conn_error'           => 'Could not connect to the database.',
                'mode_disabled'           => 'Disabled',
                'mode_name_12'            => 'Name (12)',
                'mode_steamid'            => 'SteamID',
                'mode_name'               => 'Name',
                'mode_lan_other'          => 'LAN / Other',
                'badge_mode_name'         => 'Mode: Name',
                'badge_mode_steam'        => 'Mode: SteamID',

                // Tiers
                'tier_global'             => 'Global Elite',
                'tier_supreme'            => 'Supreme',
                'tier_eagle'              => 'Eagle',
                'tier_master'             => 'Master',
                'tier_gold'               => 'Gold',
                'tier_silver'             => 'Silver',
                'tier_bronze'             => 'Bronze',
                'tier_recruit'            => 'Recruit',

                // Weapon categories & special names
                'wcat_pistols'            => 'Pistols',
                'wcat_special'            => 'Secondary / Special Weapons',
                'wcat_smg'                => 'Shotguns & SMGs',
                'wcat_rifles'             => 'Assault Rifles',
                'wcat_snipers'            => 'Snipers & Machine Guns',
                'wname_knife'             => 'Knife',
                'wname_hegrenade'         => 'HE Grenade',
                'wname_c4'                => 'C4 Bomb',

                // Home
                'home_hero_title'         => 'Statistics',
                'home_hero_desc'          => 'Track real-time performance, look up any player by SteamID or Name (max 12 chars), and explore weapons, multikills, and clutches across Multi-Server networks.',
                'home_search_ph'          => 'E.g., STEAM_0:1:12345 or Nick...',
                'kpi_players'             => 'Registered Players',
                'kpi_players_sub'         => 'SteamID & Name',
                'kpi_kills'               => 'Total Kills',
                'kpi_hs'                  => 'Headshot Accuracy',
                'kpi_hs_sub'              => '{n} headshots',
                'kpi_bombs'               => 'Bombs Planted',
                'kpi_bombs_sub'           => '{n} defused',
                'kpi_aces_clutches'       => 'ACEs / Clutches',
                'kpi_aces_sub'            => 'Highlight plays',
                'mvp_points'              => 'Leader (Skill Points)',
                'mvp_points_unit'         => 'Points',
                'mvp_kills'               => 'Top Fragger (Kills)',
                'mvp_kills_unit'          => 'Kills',
                'mvp_ace'                 => 'ACE King (5K)',
                'home_top5_title'         => 'TOP 5 Overall Ranking',
                'home_view_full_table'    => 'View full leaderboard →',
                'home_no_players'         => 'No players registered yet.',
                'home_deadliest_weapons'  => 'Deadliest Weapons',
                'home_view_all_weapons'   => 'View all weapons →',
                'home_no_weapon_kills'    => 'No weapon kills recorded yet.',
                'home_servers_title'      => 'Connected Servers (Multi-Server)',
                'home_servers_count'      => '{n} server(s)',
                'srv_online'              => 'Online',
                'srv_registered'          => 'Registered',
                'srv_save_prefix'         => 'Save:',
                'srv_last_pulse'          => 'Last heartbeat:',
                'srv_view_ranking'        => 'View Ranking →',

                // Tables & Top
                'col_player'              => 'Player',
                'col_player_steam'        => 'Player / SteamID',
                'col_name'                => 'Name',
                'col_id_type'             => 'ID Type',
                'col_rank_tier'           => 'Tier',
                'col_level'               => 'Level',
                'col_points'              => 'Points',
                'col_kills'               => 'Kills',
                'col_deaths'              => 'Deaths',
                'col_assists'             => 'Assists',
                'col_hs_pct'              => 'HS %',
                'col_c4_pd'               => 'C4 (P/D)',
                'col_wlt'                 => 'W / L / T',
                'col_wl'                  => 'W / L',
                'col_profile'             => 'Profile',
                'col_share'               => 'Share',
                'col_weapon_kills'        => 'Kills ({w})',
                'top_title'               => 'TOP Overall Ranking',
                'top_subtitle'            => 'Full player leaderboard ({n} found). Click any player to inspect their full profile.',
                'top_filter_ph'           => 'Filter by Name or SteamID...',
                'top_error'               => 'Failed to load leaderboard.',
                'top_empty'               => 'No registered players match your filter.',
                'motd_empty'              => 'No records yet.',
                'motd_error'              => 'Could not load ranking data.',
                'sort_points'             => 'Sort: Points',
                'sort_kills'              => 'Sort: Kills',
                'sort_kd'                 => 'Sort: K/D Ratio',
                'sort_hs'                 => 'Sort: Headshot %',
                'sort_wins'               => 'Sort: Wins',
                'sort_level'              => 'Sort: Level',

                // Search
                'search_title'            => 'Player Search',
                'search_subtitle'         => 'Find detailed statistics by SteamID (e.g. STEAM_0:1:...) or Name (max 12 chars).',
                'search_input_ph'         => 'Enter a SteamID (STEAM_0:...) or Name (max 12 chars)...',
                'search_mode_all'         => 'SteamID & Name',
                'search_mode_nick'        => 'Name Only',
                'search_mode_authid'      => 'SteamID Only',
                'search_error'            => 'Database search could not be completed.',
                'search_showing_query'    => 'Showing {shown} of {total} result(s) for',
                'search_showing_top'      => 'Showing featured players (type a Name or SteamID above to filter).',
                'search_empty'            => 'No players found matching',

                // Player Profile
                'player_not_found'        => 'Player Not Found',
                'player_not_found_desc'   => 'We could not locate the requested profile. Try searching by SteamID or Name.',
                'player_of_total'         => 'of {n}',
                'player_last_server'      => 'Last server:',
                'player_last_seen'        => 'Last seen:',
                'player_xp_next'          => '{pct} / 100 pts to Level {next}',
                'player_adr_label'        => 'ADR (Dmg / Round)',
                'player_adr_sub'          => 'Total dmg: {dmg} • Avg RWS: {rws}',
                'player_c4_label'         => 'C4 Objectives',
                'player_c4_sub'           => '{bp} planted • {bd} defused',
                'player_matches_box'      => 'Match & Round Performance',
                'player_matches_played'   => 'Matches Played:',
                'player_winrate'          => 'Win Rate:',
                'player_wins'             => 'Wins:',
                'player_ties'             => 'Ties:',
                'player_losses'           => 'Losses:',
                'player_rounds_total'     => 'Total Rounds:',
                'player_effectiveness'    => 'Win %:',
                'player_rounds_won'       => 'Rounds Won:',
                'player_rounds_lost'      => 'Rounds Lost:',
                'player_versus_box'       => 'Multikills & Clutches',
                'player_weapons_box'      => 'Weapon Statistics',
                'player_total_kills_sub'  => '{n} total kills',
                'player_no_weapons'       => 'This player has no recorded weapon kills yet.',

                // Versus
                'versus_title'            => 'Multikills & Clutches Ranking (Versus)',
                'versus_subtitle'         => 'Best individual plays, multi-frag rounds, and 1vX clutch victories.',
                'versus_ace_leader'       => '5K Multikill Leader',
                'versus_clutch_leader'    => '1 vs X Clutch Specialist',
                'versus_1v5_wins'         => '{n} 1v5 wins',
                'versus_filter_ph'        => 'Filter Versus ranking by Name or SteamID...',
                'versus_empty'            => 'No matching versus records found.',
                'versus_error'            => 'Failed to load Versus statistics.',

                // Weapons
                'weapons_title'           => 'Weapon Statistics',
                'weapons_subtitle'        => 'Select a weapon to inspect its top players leaderboard.',
                'weapons_quick_filter'    => 'Filter weapon (e.g. AK-47, AWP, Deagle)...',
                'weapons_kills_count'     => '{n} kills',
                'weapon_invalid'          => 'Invalid weapon.',
                'weapon_error'            => 'Could not retrieve statistics for this weapon.',
                'weapon_total_kills'      => 'Total kills with this weapon:',
                'weapon_filter_ph'        => 'Filter players on {w} by Name or SteamID...',
                'weapon_empty'            => 'No records with this weapon yet.',
            ],

            'pt' => [
                'site_subtitle'           => 'Painel Moderno de Estatísticas para Counter-Strike 1.6 • Compatível com SteamID, Nome & Multi-Server',
                'motd_subtitle'           => 'Counter-Strike 1.6 • Estatísticas Oficiais',
                'search_placeholder_hdr'  => 'Buscar por SteamID ou Nome...',
                'search_aria'             => 'Buscar jogador por SteamID ou Nome',
                'btn_search'              => 'Buscar',
                'btn_filter'              => 'Filtrar',
                'btn_apply'               => 'Aplicar',
                'btn_reset'               => 'Resetar',
                'btn_clear'               => 'Limpar',
                'btn_copy'                => 'Copiar',
                'btn_copy_steam'          => 'Copiar SteamID',
                'btn_view_profile'        => 'Ver Perfil',
                'btn_back_ranking'        => '← Voltar ao Ranking',
                'btn_search_other'        => 'Buscar outro jogador',
                'btn_all_weapons'         => '← Todas as Armas',
                'btn_go_search'           => 'Ir para a Busca',
                'discord_community'       => 'Comunidade Discord',
                'nav_home'                => 'Início',
                'nav_top'                 => 'TOP Ranking',
                'nav_ranking'             => 'Ranking',
                'nav_weapons'             => 'Armas',
                'nav_versus'              => 'Ranking Versus',
                'nav_versus_short'        => 'Versus',
                'nav_search'              => 'Buscar Jogador',
                'label_server'            => 'Servidor',
                'label_lang'              => 'Idioma',
                'all_servers'             => 'Todos os Servidores',
                'global_shared'           => 'Global Compartilhado',
                'global_short'            => 'Global',
                'per_server'              => 'Por Servidor',
                'toast_copied'            => 'SteamID copiado para a área de transferência',
                'unnamed'                 => 'Sem Nome',
                'no_steamid'              => 'Sem SteamID',
                'db_conn_error'           => 'Não foi possível conectar ao banco de dados.',
                'mode_disabled'           => 'Desativado',
                'mode_name_12'            => 'Nome (12)',
                'mode_steamid'            => 'SteamID',
                'mode_name'               => 'Nome',
                'mode_lan_other'          => 'LAN / Outro',
                'badge_mode_name'         => 'Modo: Nome',
                'badge_mode_steam'        => 'Modo: SteamID',

                // Tiers
                'tier_global'             => 'Global Elite',
                'tier_supreme'            => 'Supremo',
                'tier_eagle'              => 'Águia',
                'tier_master'             => 'Mestre',
                'tier_gold'               => 'Ouro',
                'tier_silver'             => 'Prata',
                'tier_bronze'             => 'Bronze',
                'tier_recruit'            => 'Recruta',

                // Weapon categories & special names
                'wcat_pistols'            => 'Pistolas',
                'wcat_special'            => 'Armas Secundárias / Especiais',
                'wcat_smg'                => 'Escopetas e Submetralhadoras (SMG)',
                'wcat_rifles'             => 'Rifles de Assalto',
                'wcat_snipers'            => 'Snipers e Metralhadoras',
                'wname_knife'             => 'Faca',
                'wname_hegrenade'         => 'Granada HE',
                'wname_c4'                => 'Bomba C4',

                // Home
                'home_hero_title'         => 'Estatísticas',
                'home_hero_desc'          => 'Acompanhe o desempenho em tempo real, pesquise qualquer jogador por SteamID ou Nome (máx. 12 letras) e explore armas, multikills e clutches em rede Multi-Server.',
                'home_search_ph'          => 'Ex: STEAM_0:1:12345 ou Nick...',
                'kpi_players'             => 'Jogadores Registrados',
                'kpi_players_sub'         => 'SteamID & Nome',
                'kpi_kills'               => 'Abates Totais',
                'kpi_hs'                  => 'Precisão Headshots',
                'kpi_hs_sub'              => '{n} tiros na cabeça',
                'kpi_bombs'               => 'Bombas Plantadas',
                'kpi_bombs_sub'           => '{n} desarmadas',
                'kpi_aces_clutches'       => 'ACEs / Clutches',
                'kpi_aces_sub'            => 'Jogadas em destaque',
                'mvp_points'              => 'Líder (Skill Points)',
                'mvp_points_unit'         => 'Pontos',
                'mvp_kills'               => 'Maior Abatedor (Frags)',
                'mvp_kills_unit'          => 'Abates',
                'mvp_ace'                 => 'Rei do ACE (5K)',
                'home_top5_title'         => 'TOP 5 Ranking Geral',
                'home_view_full_table'    => 'Ver tabela completa →',
                'home_no_players'         => 'Ainda não há jogadores registrados.',
                'home_deadliest_weapons'  => 'Armas Mais Letais',
                'home_view_all_weapons'   => 'Ver todas as armas →',
                'home_no_weapon_kills'    => 'Ainda não há abates registrados por arma.',
                'home_servers_title'      => 'Servidores Conectados (Multi-Server)',
                'home_servers_count'      => '{n} servidor(es)',
                'srv_online'              => 'Online',
                'srv_registered'          => 'Registrado',
                'srv_save_prefix'         => 'Salvamento:',
                'srv_last_pulse'          => 'Último pulso:',
                'srv_view_ranking'        => 'Ver Ranking →',

                // Tables & Top
                'col_player'              => 'Jogador',
                'col_player_steam'        => 'Jogador / SteamID',
                'col_name'                => 'Nome',
                'col_id_type'             => 'Tipo ID',
                'col_rank_tier'           => 'Patente',
                'col_level'               => 'Nível',
                'col_points'              => 'Pontos',
                'col_kills'               => 'Abates',
                'col_deaths'              => 'Mortes',
                'col_assists'             => 'Assist.',
                'col_hs_pct'              => '% HS',
                'col_c4_pd'               => 'C4 (P/D)',
                'col_wlt'                 => 'V / D / E',
                'col_wl'                  => 'V / D',
                'col_profile'             => 'Perfil',
                'col_share'               => 'Proporção',
                'col_weapon_kills'        => 'Abates ({w})',
                'top_title'               => 'TOP Ranking Geral',
                'top_subtitle'            => 'Classificação completa de jogadores ({n} encontrados). Clique em um jogador para ver seu perfil completo.',
                'top_filter_ph'           => 'Filtrar por Nome ou SteamID...',
                'top_error'               => 'Erro ao carregar a classificação.',
                'top_empty'               => 'Nenhum jogador encontrado com este filtro.',
                'motd_empty'              => 'Nenhum registro ainda.',
                'motd_error'              => 'Não foi possível carregar os dados do ranking.',
                'sort_points'             => 'Ordem: Pontos',
                'sort_kills'              => 'Ordem: Abates',
                'sort_kd'                 => 'Ordem: Taxa K/D',
                'sort_hs'                 => 'Ordem: % Headshot',
                'sort_wins'               => 'Ordem: Vitórias',
                'sort_level'              => 'Ordem: Nível',

                // Search
                'search_title'            => 'Busca de Jogadores',
                'search_subtitle'         => 'Encontre estatísticas detalhadas buscando por SteamID (ex. STEAM_0:1:...) ou Nome (máx. 12 caracteres).',
                'search_input_ph'         => 'Digite um SteamID (STEAM_0:...) ou Nome (máx. 12 letras)...',
                'search_mode_all'         => 'SteamID e Nome',
                'search_mode_nick'        => 'Apenas Nome',
                'search_mode_authid'      => 'Apenas SteamID',
                'search_error'            => 'Não foi possível realizar a busca no banco de dados.',
                'search_showing_query'    => 'Mostrando {shown} de {total} resultado(s) para',
                'search_showing_top'      => 'Mostrando jogadores em destaque (digite um Nome ou SteamID acima para filtrar).',
                'search_empty'            => 'Nenhum jogador encontrado para',

                // Player Profile
                'player_not_found'        => 'Jogador não encontrado',
                'player_not_found_desc'   => 'Não localizamos o perfil solicitado. Tente buscar por SteamID ou Nome.',
                'player_of_total'         => 'de {n}',
                'player_last_server'      => 'Último servidor:',
                'player_last_seen'        => 'Última atividade:',
                'player_xp_next'          => '{pct} / 100 pts para o Nível {next}',
                'player_adr_label'        => 'ADR (Dano / Rodada)',
                'player_adr_sub'          => 'Dano total: {dmg} • RWS méd.: {rws}',
                'player_c4_label'         => 'Objetivos C4',
                'player_c4_sub'           => '{bp} plantadas • {bd} desarmadas',
                'player_matches_box'      => 'Desempenho em Partidas e Rodadas',
                'player_matches_played'   => 'Partidas Jogadas:',
                'player_winrate'          => 'Win Rate:',
                'player_wins'             => 'Vitórias:',
                'player_ties'             => 'Empates:',
                'player_losses'           => 'Derrotas:',
                'player_rounds_total'     => 'Rodadas Totais:',
                'player_effectiveness'    => 'Aproveitamento:',
                'player_rounds_won'       => 'Rodadas Vencidas:',
                'player_rounds_lost'      => 'Rodadas Perdidas:',
                'player_versus_box'       => 'Multikills & Clutches',
                'player_weapons_box'      => 'Estatísticas por Arma',
                'player_total_kills_sub'  => '{n} abates totais',
                'player_no_weapons'       => 'Este jogador ainda não possui abates com armas.',

                // Versus
                'versus_title'            => 'Ranking Multikills & Clutches (Versus)',
                'versus_subtitle'         => 'Melhores jogadas individuais, abates múltiplos na rodada e vitórias em desvantagem (1 vs X).',
                'versus_ace_leader'       => 'Líder em Multikills 5K',
                'versus_clutch_leader'    => 'Especialista em 1 vs X',
                'versus_1v5_wins'         => '{n} vitórias 1v5',
                'versus_filter_ph'        => 'Filtrar ranking Versus por Nome ou SteamID...',
                'versus_empty'            => 'Nenhum registro versus correspondente.',
                'versus_error'            => 'Erro ao carregar estatísticas Versus.',

                // Weapons
                'weapons_title'           => 'Estatísticas por Arma',
                'weapons_subtitle'        => 'Selecione uma arma para consultar a classificação dos melhores jogadores.',
                'weapons_quick_filter'    => 'Filtrar arma (ex: AK-47, AWP, Deagle)...',
                'weapons_kills_count'     => '{n} abates',
                'weapon_invalid'          => 'Arma inválida.',
                'weapon_error'            => 'Não foi possível carregar os dados desta arma.',
                'weapon_total_kills'      => 'Abates totais com esta arma:',
                'weapon_filter_ph'        => 'Filtrar jogadores em {w} por Nome ou SteamID...',
                'weapon_empty'            => 'Ainda não há registros com esta arma.',
            ],
        ];

        return $dict;
    }
}

if (!function_exists('bps_t')) {
    function bps_t($key, array $vars = []) {
        $lang = bps_lang();
        $dict = bps_translations();
        $text = $dict[$lang][$key] ?? ($dict['es'][$key] ?? $key);

        if (!empty($vars)) {
            $replace = [];
            foreach ($vars as $k => $v) {
                $replace['{' . $k . '}'] = (string)$v;
            }
            $text = strtr($text, $replace);
        }
        return $text;
    }
}

bps_init_lang();
?>
