<div class="wrap">
	<h2>WP-Project</h2>
	<div id="rightnow">
		<h3 class="reallynow">
			<span>Right Now</span>
			<a class="rbutton" href="<?php $this->friendly_page_link( 'projects' ); ?>#addproject"><strong>Start a Project</strong></a>
			<a class="rbutton" href="<?php $this->friendly_page_link( 'clients' ); ?>#addclient">Enter Client</a>
			<br class="clear" />
		</h3>
		<p class="youhave">
		You have <a href="<?php $this->friendly_page_link( 'projects' ); ?>"><?php echo $this->project_count(); ?> projects</a>, 
		<a href="<?php $this->friendly_page_link( 'clients' ); ?>"><?php echo $this->client_count(); ?> clients</a>, 
		and <a href="<?php $this->friendly_page_link( 'tasks' ); ?>"><?php echo $this->task_count(); ?> tasks</a> active.
		</p>
		<p class="youare">This is WP-Project version <?php echo $this->version; ?></p>
	</div>
		
	<div id="wp_project_projects" class="dashboard-widget-holder">
		<div class="dashboard-widget">
			<h3 class="dashboard-widget-title">Projects</h3>
			<div class="dashboard-widget-content">
				<ul>
				<?php 
				$projects = $this->get_projects();
				foreach( $projects as $project ) {
					?>
					<li><strong><?php echo $project->project_title; ?></strong> &mdash; <?php echo $this->truncate( $project->project_description, 75 ); ?></li>
					 
					<?php
					$project_counter++;
					if( $project_counter >= 5 ) { break; }
				}
				?>
				</ul>
			</div>
		</div>
	</div>
	
	<div id="wp_project_clients" class="dashboard-widget-holder">
		<div class="dashboard-widget">
			<h3 class="dashboard-widget-title">Clients</h3>
			<div class="dashboard-widget-content">
			<ul>
				<?php 
				$clients = $this->get_clients();
				foreach( $clients as $client ) {
					?>
					<li><strong><?php echo $client->client_name; ?></strong> &mdash; <?php echo $this->truncate( $client->client_description, 75 ); ?></li>
					<?php
					$client_counter++;
					if( $client_counter >= 5 ) { break; }
				}
				?>
				</ul>
			</div>
		</div>
	</div>
	
	<div id="wp_project_tasks" class="dashboard-widget-holder">
		<div class="dashboard-widget">
			<h3 class="dashboard-widget-title">Tasks</h3>
			<div class="dashboard-widget-content">
			<ul>
				<?php 
				$tasks = $this->get_tasks();
				foreach( $tasks as $task ) {
					?>
					<li><strong><?php echo $task->task_name; ?></strong> &mdash; <?php echo $this->truncate( $task->task_description, 75 ); ?></li>
					<?php
					$task_counter++;
					if( $task_counter >= 5 ) { break; }
				}
				?>
				</ul>
			</div>
		</div>
	</div>
	
	<div id="wp_project_billing" class="dashboard-widget-holder">
		<div class="dashboard-widget">
			<h3 class="dashboard-widget-title">About</h3>
			<div class="dashboard-widget-content">
				<p>WP-Project is a complete project management suite developed for use from within WordPress.  It is inspired by product offerings
				like <a href="http://basecamphq.com">Basecamp</a> and <a href="http://getharvest.com">Harvest</a>.</p>
				<p>While WP-Project is not as polished as those offerings and doesn't have all of the features they do, it does have one huge
				advantage: it's free.  WP-Project is licensed under the GPL, which means you can use it and modify it for free.  Just make sure 
				to contribute any changes you make to the community.</p>
				<h3>Features</h3>
				<p>What does WP-Project offer?  The following is a brief list of features present in WP-Project:</p>
				<ul>
					<li>Track clients</li>
					<li>Track projects by client</li>
					<li>Track tasks by project</li>
					<li>Track time per task, quickly and easily</li>
				</ul>
			</div>
		</div>
	</div>
	<br class="clear" />
	
</div>