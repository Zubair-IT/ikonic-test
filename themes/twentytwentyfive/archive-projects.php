<?php get_header(); ?>

<h1>Projects</h1>

<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <div class="project-item">
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        </div>
    <?php endwhile; ?>

    <div class="pagination">
        <?php
        echo paginate_links([
            'prev_text' => '← Prev',
            'next_text' => 'Next →',
        ]);
        ?>
    </div>

<?php else : ?>
    <p>No projects found.</p>
<?php endif; ?>

<?php get_footer(); ?>
