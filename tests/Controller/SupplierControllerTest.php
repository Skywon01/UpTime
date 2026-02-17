<?php

namespace App\Tests\Controller;

use App\Entity\Supplier;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SupplierControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $supplierRepository;
    private string $path = '/supplier/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->supplierRepository = $this->manager->getRepository(Supplier::class);

        foreach ($this->supplierRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Supplier index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'supplier[name]' => 'Testing',
            'supplier[contactName]' => 'Testing',
            'supplier[email]' => 'Testing',
            'supplier[phoneNumber]' => 'Testing',
            'supplier[website]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->supplierRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Supplier();
        $fixture->setName('My Title');
        $fixture->setContactName('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPhoneNumber('My Title');
        $fixture->setWebsite('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Supplier');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Supplier();
        $fixture->setName('Value');
        $fixture->setContactName('Value');
        $fixture->setEmail('Value');
        $fixture->setPhoneNumber('Value');
        $fixture->setWebsite('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'supplier[name]' => 'Something New',
            'supplier[contactName]' => 'Something New',
            'supplier[email]' => 'Something New',
            'supplier[phoneNumber]' => 'Something New',
            'supplier[website]' => 'Something New',
        ]);

        self::assertResponseRedirects('/supplier/');

        $fixture = $this->supplierRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getContactName());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getPhoneNumber());
        self::assertSame('Something New', $fixture[0]->getWebsite());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Supplier();
        $fixture->setName('Value');
        $fixture->setContactName('Value');
        $fixture->setEmail('Value');
        $fixture->setPhoneNumber('Value');
        $fixture->setWebsite('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/supplier/');
        self::assertSame(0, $this->supplierRepository->count([]));
    }
}
