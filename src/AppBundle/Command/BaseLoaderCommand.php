<?php


namespace AppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class BaseLoaderCommand extends ContainerAwareCommand
{
    /**
     * @param string $fileName
     * @return array
     */
    public function getCsvData(string $fileName){
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder(',')]);

        $fileContent = $this->getFileContent($fileName);

        return $serializer->decode(
            $fileContent,
            'csv'
        );
    }

    /**
     * @param $filePath
     * @return string
     */
    function getFileContent($filePath)
    {
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException(sprintf('File %s not found', $filePath));
        }


        $content = file_get_contents($filePath);
        return mb_convert_encoding($content, 'UTF-8',
            mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
    }
}