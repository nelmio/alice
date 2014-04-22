# since using php in this way is generally bad practice, it's not a huge
# problem, but the first newline following ?> is removed, and thus requires
# a second newline for php echo statement of this type

contextual: <?php echo $context['value']; ?>

username: <username()>
