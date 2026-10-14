<?php

it('returns a successful response', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Bookworm')
        ->assertSee('Find your next favorite book, and share the stories you love.')
        ->assertSee('Pride and Prejudice by Jane Austen, The Hobbit by J. R. R. Tolkien, and Little Women by Louisa May Alcott')
        ->assertSee('JANE AUSTEN')
        ->assertSee('J. R. R. TOLKIEN')
        ->assertSee('LOUISA MAY ALCOTT')
        ->assertSee('hover:scale-110')
        ->assertSee('Join to explore books')
        ->assertSee('Join Bookworm');
});
