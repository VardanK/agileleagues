<?php

define('TEAM_ID_1', 1);
define('TEAM_ID_2', 2);
define('TEAM_ID_EMPTY', 3);

define('DEVELOPER_ID_1', 1);
define('DEVELOPER_ID_2', 2);
define('DEVELOPER_3_ID', 3);
define('DEVELOPER_4_ID', 4);
define('SCRUMMASTER_ID_1', 5);
define('SCRUMMASTER_ID_2', 6);
define('PRODUCT_OWNER_ID', 7);

define('XP_TO_REACH_LEVEL_10', 2200);
define('XP_TO_REACH_LEVEL_20', 8000);

class TestUtils {

    private $models = array(
        'Configuration',
        'XpLog',    
        'EventCompleteLog',
        'EventTaskLog',
        'EventTask',
        'EventJoinLog',
        'EventActivity',
        'Notification',
        'Timeline',
        'LogVote',
        'Log', 
        'BadgeLog', 
        'BadgeRequisite',
        'ActivityRequisite',
        'Badge', 
        'Activity', 
        'Domain', 
        'Event',
        'EventType',
        'Team',
        'Player',
        'PlayerType'
    );
    
    private $views = array(
        'PlayerActivityCoins',
        'PlayerTotalActivityCoins',
        'LastWeekLog',
        'BadgeClaimed',
        'BadgeActivityProgress'
    );

    public function __construct() {
        foreach ($this->models as $model) {
            $this->$model = ClassRegistry::init($model);
        }
        foreach ($this->views as $model) {
            $this->$model = ClassRegistry::init($model);
        }
    }

    public function clearDatabase() {
        foreach ($this->models as $model) {
            $deleted = $this->$model->deleteAll(array($model . '.id <>' => 0), false);
            $table = $this->$model->table;
        }
    }

    public function generatePO($name) {
        $saved = $this->generatePlayer($name, PLAYER_TYPE_PRODUCT_OWNER);
        return $saved;
    }

    public function generatePlayer($name = 'Player', $type = PLAYER_TYPE_DEVELOPER) {
        $this->generatePlayerTypes();
        $email = md5($name) . '@email.com';
        $saved = $this->Player->save(array('Player' => array(
            'name' => $name, 
            'player_type_id' => $type,
            'email' => $email,
            'password' => 123456,
            'repeat_password' => 123456,
            'team_id' => null
        )));
        return $saved;
    }

    public function generatePlayerTypes() {
        if ($this->PlayerType->find('count') === 0) { 
            $this->PlayerType->saveMany(array(
                array('id' => PLAYER_TYPE_DEVELOPER, 'name' => 'Developer'),
                array('id' => PLAYER_TYPE_SCRUMMASTER, 'name' => 'ScrumMaster'),
                array('id' => PLAYER_TYPE_PRODUCT_OWNER, 'name' => 'Product Owner'),
            ));
        }
    }

    public function generatePlayers() {
        $this->generatePlayerTypes();

        $this->Player->saveMany(array(
            array('id' => DEVELOPER_ID_1, 'player_type_id' => PLAYER_TYPE_DEVELOPER, 'name' => 'Developer 1', 'email' => 'email1@email.com', 'password' => '123456', 'repeat_password' => '123456', 'xp' => 500, 'verified_in' => date('Y-m-d H:i:s')),
            array('id' => DEVELOPER_ID_2, 'player_type_id' => PLAYER_TYPE_DEVELOPER, 'name' => 'Developer 2', 'email' => 'email2@email.com', 'password' => '123456', 'repeat_password' => '123456', 'xp' => 100, 'verified_in' => date('Y-m-d H:i:s')),
            array('id' => DEVELOPER_3_ID, 'player_type_id' => PLAYER_TYPE_DEVELOPER, 'name' => 'Developer 3', 'email' => 'email3@email.com', 'password' => '123456', 'repeat_password' => '123456', 'xp' => 100, 'verified_in' => date('Y-m-d H:i:s')),
            // Account not verified
            array('id' => DEVELOPER_4_ID, 'player_type_id' => PLAYER_TYPE_DEVELOPER, 'name' => 'Developer 4', 'email' => 'email3@email.com', 'password' => '123456', 'repeat_password' => '123456', 'xp' => 100, 'verified_in' => null),
            array('id' => SCRUMMASTER_ID_1, 'player_type_id' => PLAYER_TYPE_SCRUMMASTER, 'name' => 'ScrumMaster 1', 'email' => 'scrummaster1@email.com', 'password' => '123456', 'repeat_password' => '123456', 'xp' => 999, 'verified_in' => date('Y-m-d H:i:s')),
            array('id' => SCRUMMASTER_ID_2, 'player_type_id' => PLAYER_TYPE_SCRUMMASTER, 'name' => 'ScrumMaster 2', 'email' => 'scrummaster2@email.com', 'password' => '123456', 'repeat_password' => '123456', 'xp' => 999, 'verified_in' => date('Y-m-d H:i:s')),
            array('id' => PRODUCT_OWNER_ID, 'player_type_id' => PLAYER_TYPE_PRODUCT_OWNER, 'name' => 'PO', 'email' => 'po@email.com', 'password' => '123456', 'repeat_password' => '123456', 'xp' => 999, 'verified_in' => date('Y-m-d H:i:s')),
        ));

        $this->Team->updateAll(
            array('Team.player_id_scrummaster' => SCRUMMASTER_ID_1), 
            array('Team.id' => array(TEAM_ID_1, TEAM_ID_2))
        );

        $this->Player->updateAll(
            array('team_id' => TEAM_ID_1), 
            array('Player.id' => array(DEVELOPER_ID_1, DEVELOPER_ID_2, PRODUCT_OWNER_ID))
        );
        $this->Player->updateAll(
            array('team_id' => TEAM_ID_2), 
            array('Player.id' => array(DEVELOPER_3_ID, DEVELOPER_4_ID))
        );
    }

    public function generateTeams() {
        $this->Team->saveMany(array(
            array('id' => TEAM_ID_1, 'name' => 'Team 1'),
            array('id' => TEAM_ID_2, 'name' => 'Team 2'),
            array('id' => TEAM_ID_EMPTY, 'name' => 'Team Empty'),
        ));
    }

    public function generateDomains() {
        $this->Domain->saveMany(array(
            array('id' => 1, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Domain 1', 'description' => 'Domain description...', 'abbr' => 'DM1', 'color' => '#aaaaaa', 'player_type_id' => PLAYER_TYPE_DEVELOPER),
            array('id' => 2, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Domain 2', 'description' => 'Domain description...', 'abbr' => 'DM2', 'color' => '#bbbbbb', 'player_type_id' => PLAYER_TYPE_DEVELOPER),
            array('id' => 3, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'SM Domain', 'description' => 'SM Domain description...', 'abbr' => 'SM', 'color' => '#cccccc', 'player_type_id' => PLAYER_TYPE_SCRUMMASTER),
            array('id' => 4, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'PO Domain', 'description' => 'PO Domain description...', 'abbr' => 'PO', 'color' => '#dddddd', 'player_type_id' => PLAYER_TYPE_PRODUCT_OWNER)
        ));
    }

    public function generateActivities() {
        $this->Activity->saveMany(array(
            array('id' => 1, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Activity 1', 'reported' => 1, 'acceptance_votes' => 1, 'rejection_votes' => 2, 'domain_id' => 1, 'xp' => rand(5, 100)),
            array('id' => 2, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Activity 2', 'reported' => 10, 'acceptance_votes' => 2, 'rejection_votes' => 1, 'domain_id' => 1, 'xp' => rand(5, 100)),
            array('id' => 3, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Activity 3', 'reported' => 100, 'acceptance_votes' => 1, 'rejection_votes' => 2, 'domain_id' => 1, 'xp' => rand(5, 100)),
            array('id' => 4, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Activity 4', 'reported' => 1000, 'acceptance_votes' => 2, 'rejection_votes' => 1, 'domain_id' => 1, 'xp' => rand(5, 100)),
            array('id' => 5, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Activity 5', 'reported' => 10000, 'acceptance_votes' => 1, 'rejection_votes' => 2, 'domain_id' => 2, 'xp' => rand(5, 100)),
            array('id' => 6, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Activity 6', 'reported' => 100000, 'acceptance_votes' => 2, 'rejection_votes' => 1, 'domain_id' => 2, 'xp' => rand(5, 100)),
            array('id' => 7, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Activity 7', 'reported' => 1000000, 'acceptance_votes' => 1, 'rejection_votes' => 2, 'domain_id' => 2, 'xp' => rand(5, 100)),
            array('id' => 8, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Activity 8', 'reported' => 10000000, 'acceptance_votes' => 2, 'rejection_votes' => 1, 'domain_id' => 2, 'xp' => XP_TO_REACH_LEVEL_10),
            array('id' => 9, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Activity 9', 'reported' => 100000000, 'acceptance_votes' => 1, 'rejection_votes' => 2, 'domain_id' => 2, 'xp' => XP_TO_REACH_LEVEL_20),
            array('id' => 10, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Activity 10', 'reported' => 0, 'acceptance_votes' => 2, 'rejection_votes' => 1, 'domain_id' => 2, 'xp' => 1000),
            array('id' => 11, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'SM Activity 1', 'reported' => 0, 'acceptance_votes' => 1, 'rejection_votes' => 2, 'domain_id' => 3, 'xp' => 1000),
            array('id' => 12, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'SM Activity 2', 'reported' => 0, 'acceptance_votes' => 2, 'rejection_votes' => 1, 'domain_id' => 3, 'xp' => 1000),
            array('id' => 13, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'PO Activity 1', 'reported' => 0, 'acceptance_votes' => 1, 'rejection_votes' => 2, 'domain_id' => 4, 'xp' => 1000),
            array('id' => 14, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'PO Activity 2', 'reported' => 0, 'acceptance_votes' => 2, 'rejection_votes' => 1, 'domain_id' => 4, 'xp' => 1000),
        ));
    }

    public function generateInactiveActivities() {
        $this->Activity->saveMany(array(
            array('id' => 15, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Inactive Activity 1', 'domain_id' => 1, 'inactive' => 1),
            array('id' => 16, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Inactive Activity 2', 'domain_id' => 1, 'inactive' => 1),
            array('id' => 17, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Inactive Activity 3', 'domain_id' => 1, 'inactive' => 1),
            array('id' => 18, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Inactive Activity 4', 'domain_id' => 1, 'inactive' => 1),
            array('id' => 19, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Inactive Activity 5', 'domain_id' => 2, 'inactive' => 1),
            array('id' => 20, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Inactive Activity 6', 'domain_id' => 2, 'inactive' => 1),
            array('id' => 21, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Inactive Activity 7', 'domain_id' => 2, 'inactive' => 1),
            array('id' => 22, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Inactive Activity 8', 'domain_id' => 2, 'inactive' => 1),
        ));
    }


    public function generateBadges() {
        $this->Badge->saveMany(array(
            array('id' => 1, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Badge 1', 'domain_id' => 1, 'abbr' => 'BG1'),
            array('id' => 2, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Badge 2', 'domain_id' => 1, 'abbr' => 'BG2'),
            array('id' => 3, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Badge 3', 'domain_id' => 2, 'abbr' => 'BG3'),
            array('id' => 4, 'player_id_owner' => SCRUMMASTER_ID_1, 'name' => 'Badge 4', 'domain_id' => 2, 'abbr' => 'BG4'),
        ));
    }

    public function generateBadgeRequisites() {
        $this->BadgeRequisite->saveMany(array(
            array('badge_id' => 4, 'badge_id_requisite' => 3),
            array('badge_id' => 3, 'badge_id_requisite' => 2),
            array('badge_id' => 2, 'badge_id_requisite' => 1)
        ));
    }

    public function generateActivityRequisites() {
        $this->ActivityRequisite->saveMany(array(
            array('badge_id' => 1, 'activity_id' => 1, 'count' => 1),
            array('badge_id' => 2, 'activity_id' => 2, 'count' => 1),
            array('badge_id' => 3, 'activity_id' => 3, 'count' => 1),
            array('badge_id' => 4, 'activity_id' => 4, 'count' => 1)
        ));
    }

    public function generateNotifications() {
        $this->Notification->saveMany(array(
            array('id' => 1, 'title' => 'Notification title', 'player_id' => DEVELOPER_ID_1, 'type' => 'success', 'read' => 0),
            array('id' => 2, 'title' => 'Notification title', 'player_id' => DEVELOPER_ID_1, 'type' => 'success', 'read' => 0),
            array('id' => 3, 'title' => 'Notification title', 'player_id' => DEVELOPER_ID_2, 'type' => 'success', 'read' => 0),
            array('id' => 4, 'title' => 'Notification title', 'player_id' => DEVELOPER_ID_2, 'type' => 'success', 'read' => 0),
            array('id' => 5, 'title' => 'Notification title', 'player_id' => DEVELOPER_ID_2, 'type' => 'success', 'read' => 1),
            array('id' => 6, 'title' => 'Notification title', 'player_id' => DEVELOPER_ID_2, 'type' => 'success', 'read' => 1),
            array('id' => 7, 'title' => 'Notification title', 'player_id' => DEVELOPER_ID_1, 'type' => 'success', 'read' => 1),
            array('id' => 8, 'title' => 'Notification title', 'player_id' => DEVELOPER_ID_1, 'type' => 'success', 'read' => 1),
        ));
    }



    public function generateBadgeLogs() {
        $this->BadgeLog->saveMany(array(
            array('badge_id' => 1, 'player_id' => DEVELOPER_ID_1),
            array('badge_id' => 2, 'player_id' => DEVELOPER_ID_1),
            array('badge_id' => 3, 'player_id' => DEVELOPER_ID_1),
            array('badge_id' => 4, 'player_id' => DEVELOPER_ID_1),
            array('badge_id' => 1, 'player_id' => DEVELOPER_ID_2),
            array('badge_id' => 2, 'player_id' => DEVELOPER_ID_2),
            array('badge_id' => 3, 'player_id' => DEVELOPER_ID_2),
            array('badge_id' => 4, 'player_id' => DEVELOPER_ID_2),
        ));
    }

    public function generateLogs() {
        $currentDate = (new DateTime())->format('Y-m-d');
        
        $lastWeek = new DateTime();
        $lastWeek->modify('-7 day');
        $lastWeek = $lastWeek->format('Y-m-d');

        $lastMonth = new DateTime();
        $lastMonth->modify('-1 month - 1 day');
        $lastMonth = $lastMonth->format('Y-m-d');

        $this->Log->saveMany(array(
            array('activity_id' => 1, 'player_id' => DEVELOPER_ID_1, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => $currentDate),
            array('activity_id' => 2, 'player_id' => DEVELOPER_ID_1, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => $currentDate),
            array('activity_id' => 3, 'player_id' => DEVELOPER_ID_1, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => $lastWeek),
            array('activity_id' => 4, 'player_id' => DEVELOPER_ID_1, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => $lastMonth),
            array('activity_id' => 5, 'player_id' => DEVELOPER_ID_2, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => $currentDate),
            array('activity_id' => 6, 'player_id' => DEVELOPER_ID_2, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => $currentDate),
            array('activity_id' => 7, 'player_id' => DEVELOPER_ID_2, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => $lastWeek),
            array('activity_id' => 8, 'player_id' => DEVELOPER_ID_2, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => $lastMonth),
        ), array('validate' => false));
        $result = $this->Log->query('UPDATE log SET reviewed = NOW()');
    }

    public function generateLogs2() {
        $this->Log->saveMany(array(
            array('activity_id' => 8, 'player_id' => DEVELOPER_ID_1, 'player_id_owner' => SCRUMMASTER_ID_1, 'xp' => XP_TO_REACH_LEVEL_10, 'acquired' => date('Y-m-d H:i:s')),
            array('activity_id' => 9, 'player_id' => DEVELOPER_ID_1, 'player_id_owner' => SCRUMMASTER_ID_1, 'xp' => XP_TO_REACH_LEVEL_20, 'acquired' => date('Y-m-d H:i:s')),
        ), array('validate' => false));
        $result = $this->Log->query('UPDATE log SET reviewed = NOW()');     
    }

    public function generateLogsNotReviewed() {
        $this->Log->saveMany(array(
            array('activity_id' => 1, 'player_id' => DEVELOPER_ID_1, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => '2014-01-01'),
            array('activity_id' => 2, 'player_id' => DEVELOPER_ID_1, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => '2014-01-01'),
            array('activity_id' => 3, 'player_id' => DEVELOPER_ID_1, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => '2014-01-01'),
            array('activity_id' => 4, 'player_id' => DEVELOPER_ID_1, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => '2014-01-01'),
            array('activity_id' => 5, 'player_id' => DEVELOPER_ID_2, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => '2014-01-01'),
            array('activity_id' => 6, 'player_id' => DEVELOPER_ID_2, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => '2014-01-01'),
            array('activity_id' => 7, 'player_id' => DEVELOPER_ID_2, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => '2014-01-01'),
            array('activity_id' => 8, 'player_id' => DEVELOPER_ID_2, 'player_id_owner' => SCRUMMASTER_ID_1, 'acquired' => '2014-01-01'),
        ), array('validate' => false));
    }

    public function generateEvents() {
        $this->EventType->saveMany(array(
            array('id' => EVENT_TYPE_MISSION, 'Mission', 'level_required' => EVENT_LEVEL_REQUIRED_MISSION),
            array('id' => EVENT_TYPE_CHALLENGE, 'Challenge', 'level_required' => EVENT_LEVEL_REQUIRED_CHALLENGE),
        ));
        
        $today = (new DateTime())->format('Y-m-d');
        $lastWeek = (new DateTime())->modify('-7 day')->format('Y-m-d');
        $nextWeek = (new DateTime())->modify('+7 day')->format('Y-m-d');
        $lastMonth = (new DateTime())->modify('-1 month')->format('Y-m-d');
        $nextMonth = (new DateTime())->modify('+1 month')->format('Y-m-d');

        $this->Event->saveMany(array(
            array('id' => 1, 'player_id_owner' => SCRUMMASTER_ID_1, 'event_type_id' => EVENT_TYPE_MISSION, 'name' => 'Active Mission', 'start' => $today, 'end' => $nextWeek),
            array('id' => 2, 'player_id_owner' => SCRUMMASTER_ID_1, 'event_type_id' => EVENT_TYPE_MISSION, 'name' => 'Future Mission', 'start' => $nextWeek, 'end' => $nextMonth),
            array('id' => 3, 'player_id_owner' => SCRUMMASTER_ID_1, 'event_type_id' => EVENT_TYPE_MISSION, 'name' => 'Past Mission', 'start' => $lastMonth, 'end' => $lastWeek),
            array('id' => 4, 'player_id_owner' => SCRUMMASTER_ID_1, 'event_type_id' => EVENT_TYPE_CHALLENGE, 'name' => 'Active Challenge', 'start' => $today, 'end' => $nextWeek),
            array('id' => 5, 'player_id_owner' => SCRUMMASTER_ID_1, 'event_type_id' => EVENT_TYPE_CHALLENGE, 'name' => 'Future Challenge', 'start' => $nextWeek, 'end' => $nextMonth),
            array('id' => 6, 'player_id_owner' => SCRUMMASTER_ID_1, 'event_type_id' => EVENT_TYPE_CHALLENGE, 'name' => 'Past Challenge', 'start' => $lastMonth, 'end' => $lastWeek),
        ), array('validate' => false));
    }

    public function generateEventTasks() {
        $this->EventTask->saveMany(array(
            array('id' => 1, 'event_id' => 1, 'name' => 'Task 1', 'description' => 'Description 1', 'xp' => rand(0, 100)),
            array('id' => 2, 'event_id' => 2, 'name' => 'Task 2', 'description' => 'Description 2', 'xp' => rand(0, 100)),
            array('id' => 3, 'event_id' => 3, 'name' => 'Task 3', 'description' => 'Description 3', 'xp' => rand(0, 100)),
            array('id' => 4, 'event_id' => 4, 'name' => 'Task 4', 'description' => 'Description 4', 'xp' => rand(0, 100)),
            array('id' => 5, 'event_id' => 5, 'name' => 'Task 5', 'description' => 'Description 5', 'xp' => rand(0, 100)),
            array('id' => 6, 'event_id' => 6, 'name' => 'Task 6', 'description' => 'Description 6', 'xp' => rand(0, 100))
        ), array('validate' => false));
    }

    public function generateEventActivities() {
        $this->EventActivity->saveMany(array(
            array('id' => 1, 'event_id' => 1, 'activity_id' => 1, 'count' => 1),
            array('id' => 2, 'event_id' => 2, 'activity_id' => 2, 'count' => 1),
            array('id' => 3, 'event_id' => 3, 'activity_id' => 3, 'count' => 1),
            array('id' => 4, 'event_id' => 4, 'activity_id' => 4, 'count' => 1),
            array('id' => 5, 'event_id' => 5, 'activity_id' => 5, 'count' => 1),
            array('id' => 6, 'event_id' => 6, 'activity_id' => 6, 'count' => 1)
        ), array('validate' => false));
    }

    public function generateEventActivityLogs() {
        $this->Log->saveMany(array(
            array('event_id' => 1, 'activity_id' => 1, 'reviewed' => date('Y-m-d'), 'player_id' => DEVELOPER_ID_2),
            array('event_id' => 2, 'activity_id' => 2, 'reviewed' => date('Y-m-d'), 'player_id' => DEVELOPER_ID_2),
            array('event_id' => 3, 'activity_id' => 3, 'reviewed' => date('Y-m-d'), 'player_id' => DEVELOPER_ID_2),
            array('event_id' => 4, 'activity_id' => 4, 'reviewed' => date('Y-m-d'), 'player_id' => DEVELOPER_ID_2),
            array('event_id' => 5, 'activity_id' => 5, 'reviewed' => date('Y-m-d'), 'player_id' => DEVELOPER_ID_2),
            array('event_id' => 6, 'activity_id' => 6, 'reviewed' => date('Y-m-d'), 'player_id' => DEVELOPER_ID_2)
        ), array('validate' => false));
    }

    public function generateEventJoinLogs() {
        $this->EventJoinLog->saveMany(array(
            array('event_id' => 1, 'player_id' => DEVELOPER_ID_2),
            array('event_id' => 2, 'player_id' => DEVELOPER_ID_2),
            array('event_id' => 3, 'player_id' => DEVELOPER_ID_2)
        ), array('validate' => false));
    }

    public function generateEventTaskLogs() {
        $this->EventTaskLog->saveMany(array(
            array('event_id' => 1, 'event_task_id' => 1, 'player_id' => DEVELOPER_ID_2),
            array('event_id' => 2, 'event_task_id' => 2, 'player_id' => DEVELOPER_ID_2),
            array('event_id' => 3, 'event_task_id' => 3, 'player_id' => DEVELOPER_ID_2)
        ), array('validate' => false));
    }

}