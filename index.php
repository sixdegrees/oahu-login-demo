<?php require_once('config.php') ?>
<html>
  <head>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
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

      Oahu.init({ appId: "<?php echo $config['oahu']['appId'] ?>" });

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
        <button class="btn btn-primary" data-oahu-action="badge.achieve">Unlock !</button>
      {{/if}}
    {{else}}
    You must login first
    {{/if}}
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
    <div class="jumbotron">
      <h3>Current Account (server side)</h3>
      <pre class="current-user"><?php print_r(json_encode($current_account, JSON_PRETTY_PRINT)); ?></pre>
    </div>

  </body>
</html>
