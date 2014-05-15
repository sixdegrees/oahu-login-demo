<?php require_once('config.php') ?>
<!doctype html>
<html>
  <head>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="//<?php echo $config['oahu']['host'] ?>/assets/oahu.js"></script>
    <script src="//<?php echo $config['oahu']['host'] ?>/assets/oahu-apps.js"></script>
    <script>
    $(function() {

      Oahu.bind('oahu:connect:(login|logout):success', function() {
        // To reload on auth change
        // document.location.reload();
        // 
        // OR 
        // 
        // To get the current user via ajax on auth change
        $.get('/me.php').then(function(me) {
          $('.current-user').text(JSON.stringify(me, null, '\t'));
        });
      });

      Oahu.bind('oahu:account:.*', function() {
        var args = [].slice.call(arguments);
        console.log('------------------------');
        console.log("[CLIENT]", args[0])
        console.log(JSON.stringify(args[1]));
      });

      Oahu.init({ 
        callback_url: 'http://google.com',
        appId: "<?php echo $config['oahu']['appId'] ?>", 
        debug: true, 
        // verbose: true 
      });

      Oahu.Apps.register('badge', {
        templates: ['badge'],
        namespace: 'badge',
        refresh_events : ['oahu:account:success'],
        attrs: ['achievementId'],
        initialize: function() {
          if (Oahu.account) {
            this.badge = Oahu.account.player.badges[this.achievementId];
          }
        },
        achieve: function($el) {
          var self = this;
          $el.attr('disabled', true);
          var score = $('input[name="score"]').val() || 0;
          $.post('./achieve.php', { score: score }).then(function(badge) {
            if (badge) {
              self.badge = badge;
              self.render();
            } else {
              $el.attr('disabled', false);
            }

            // Emit an event for the leaderboard to refresh...
            Oahu.trigger('game:achieved');
          }, function() { $el.attr('disabled', false); });
        },
        debugResetPlayer: function() {
          var self = this;
          $.ajax({ 
            url: './achieve.php', 
            type: 'delete', 
            success: function(reset) {
              document.location.reload();  
            }
          });
        }
      });
      Oahu.Apps.register('leaderboard', {
        templates: ['leaders'],
        namespace: 'leaders',
        refresh_events: ['oahu:account:success', 'game:achieved'],
        attrs: ['achievementId'],
        initialize: function() {
        },
        getData: function(render) {
          var self = this;
          Oahu.app.getLeaderboard({ id: this.achievementId }, function(leaderboard) {
            self.leaderboard = leaderboard;
            render();
          });
        }
      });

      Oahu.Apps.register('friends_list', {
        templates: ['friends_list'],
        namespace: 'friends_list',
        refresh_events: ['oahu:account:success'],
        attrs: ['achievementId'],
        initialize: function() {
        },
        getData: function(render) {
          var self = this;
          Oahu.app.getFriends({}, function(friends) {
            var friendsList = _.compact(_.map(friends, function(friend) {
              var score, badge = friend.badges[self.achievementId];
              if (badge && badge.data) {
                score = parseInt((badge.data.score || 0), 10);
                return { name: friend.name, picture: friend.picture, score: score };
              }
            }));
            self.friends = _.sortBy(friendsList, function(friend) {
              return -1 * friend.score;
            });
            render();
          });
        }
      });

    });
    </script>
  </head>
  <body class="container">
    <!-- Badge template -->
    <script type="text/template" data-oahu-template="badge">
    {{#if account}}
      {{#if badge}}
        <h3>Score: {{badge.data.score}}</h3>
        <pre>{{json badge}}</pre>
        <a class="btn btn-danger" data-oahu-action="badge.debugResetPlayer">Reset !</a>
      {{else}}
        <input type="number" name="score" value="" placeholder="Score"  class="form-control" />
        <button class="btn btn-primary" data-oahu-action="badge.achieve">Unlock <? echo getenv('OAHU_ACHIEVEMENT_ID'); ?>!</button>
      {{/if}}
    {{else}}
    You must login first
    {{/if}}
    </script>

    <script type="text/template" data-oahu-template="leaders">
    <h1>Hey {{account.id}}</h1>
    {{#leaderboard}}
    <h3>Leaderboard {{name}} on {{total_members}}</h3>
    {{#if current_player_rank}}
    <h5>You current rank is {{current_player_rank}}</h5>
    {{else}}
    <h5>Play to get a rank !</h5>
    {{/if}}
    <ul>
      {{#players}}
      <li>
        <h5>Rank: {{rank}} - Score: {{score}}</h5>
        <img src="{{picture}}" />{{name}}
      </li>
      {{/players}}
    </ul>
    {{/leaderboard}}
    </script>

    <script type="text/template" data-oahu-template="friends_list">
    <h4>You Facebook friends who participated</h4>
    <ul>
    {{#friends}}
      <li>
        <img src="{{picture}}" /> - {{name}} - Score: {{score}}
      </li>
    {{/friends}}
    </ul>
    </script>

    <!-- optional identity widget template override -->
    <script type="text/template" data-oahu-template="identity">
    {{#account}}
      <div class='account'>
        <img class='avatar' src="{{picture}}">
        <span class="name">{{name}}</span>
        <a class="btn" data-oahu-action="disconnect">Logout</a>
      </div>
    {{/account}}
    {{^account}}
      <div class='connect'>
        <a href="#" data-oahu-action="connect" data-oahu-provider="facebook">Login with Facebook</a>
      </div>
    {{/account}}
    </script>


    <div class="row">

      <!-- With a simple identity widget -->


      <div class="col-md-4">
        <h3>With a simple identity widget</h3>
        <div class="well" data-oahu-widget="identity"></div>
      </div>

      <!-- with jquery only -->

      <div class="col-md-4">
        <h3>with jquery only</h3>
        <div class="well">
          <span class="user-name"></span>
          <span class="user-email"></span>
          <a class="user-auth btn"></a>
        </div>
        <script>
        $(function() {

          Oahu.bind('oahu:account:success', function() {
            $('.user-name').text(Oahu.account.name || "");
            $('.user-email').text(Oahu.account.email  || "");
            $('.user-auth').text(Oahu.account ? "Logout" : "Login with Facebook");
          });
          $('.user-auth').click(function(e) {
            if (Oahu.account) {
              Oahu.logout();
            } else {
              Oahu.login('facebook');
            }
          });
        });
        </script>
      </div>
      <div class="col-md-4">
        <h3>Achieve & Badge</h3>
        <div class="well" data-oahu-widget="badge" data-oahu-achievement-id="<?php echo getenv('OAHU_ACHIEVEMENT_ID') ?>"></div>
      </div>


    </div>
    <div class="row">
      <div class="well col-md-4">
        <h3>Current Account (server side)</h3>
        <pre class="current-user"><?php print_r(json_encode($current_account)); ?></pre>
      </div>
      <div class="well col-md-4" data-oahu-widget="leaderboard" data-oahu-achievement-id="<?php echo getenv('OAHU_ACHIEVEMENT_ID');?>">
      </div>
      <div class="well col-md-4" data-oahu-widget="friends_list" data-oahu-achievement-id="<?php echo getenv('OAHU_ACHIEVEMENT_ID');?>">
      </div>
    </div>

  </body>
</html>
