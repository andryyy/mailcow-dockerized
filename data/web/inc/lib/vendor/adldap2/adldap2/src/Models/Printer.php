<?php

namespace Adldap\Models;

/**
 * Class Printer.
 *
 * Represents an LDAP printer.
 */
class Printer extends Entry
{
    /**
     * Returns the printers name.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679385(v=vs.85).aspx
     *
     * @return string
     */
    public function getPrinterName()
    {
        return $this->getFirstAttribute($this->schema->printerName());
    }

    /**
     * Returns the printers share name.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679408(v=vs.85).aspx
     *
     * @return string
     */
    public function getPrinterShareName()
    {
        return $this->getFirstAttribute($this->schema->printerShareName());
    }

    /**
     * Returns the printers memory.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679396(v=vs.85).aspx
     *
     * @return string
     */
    public function getMemory()
    {
        return $this->getFirstAttribute($this->schema->printerMemory());
    }

    /**
     * Returns the printers URL.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getFirstAttribute($this->schema->url());
    }

    /**
     * Returns the printers location.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms676839(v=vs.85).aspx
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->getFirstAttribute($this->schema->location());
    }

    /**
     * Returns the server name that the
     * current printer is connected to.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679772(v=vs.85).aspx
     *
     * @return string
     */
    public function getServerName()
    {
        return $this->getFirstAttribute($this->schema->serverName());
    }

    /**
     * Returns true / false if the printer can print in color.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679382(v=vs.85).aspx
     *
     * @return null|bool
     */
    public function getColorSupported()
    {
        return $this->convertStringToBool(
            $this->getFirstAttribute(
                $this->schema->printerColorSupported()
            )
        );
    }

    /**
     * Returns true / false if the printer supports duplex printing.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679383(v=vs.85).aspx
     *
     * @return null|bool
     */
    public function getDuplexSupported()
    {
        return $this->convertStringToBool(
            $this->getFirstAttribute(
                $this->schema->printerDuplexSupported()
            )
        );
    }

    /**
     * Returns an array of printer paper types that the printer supports.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679395(v=vs.85).aspx
     *
     * @return array
     */
    public function getMediaSupported()
    {
        return $this->getAttribute($this->schema->printerMediaSupported());
    }

    /**
     * Returns true / false if the printer supports stapling.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679410(v=vs.85).aspx
     *
     * @return null|bool
     */
    public function getStaplingSupported()
    {
        return $this->convertStringToBool(
            $this->getFirstAttribute(
                $this->schema->printerStaplingSupported()
            )
        );
    }

    /**
     * Returns an array of the printers bin names.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679380(v=vs.85).aspx
     *
     * @return array
     */
    public function getPrintBinNames()
    {
        return $this->getAttribute($this->schema->printerBinNames());
    }

    /**
     * Returns the printers maximum resolution.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679391(v=vs.85).aspx
     *
     * @return string
     */
    public function getPrintMaxResolution()
    {
        return $this->getFirstAttribute($this->schema->printerMaxResolutionSupported());
    }

    /**
     * Returns the printers orientations supported.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679402(v=vs.85).aspx
     *
     * @return string
     */
    public function getPrintOrientations()
    {
        return $this->getFirstAttribute($this->schema->printerOrientationSupported());
    }

    /**
     * Returns the driver name of the printer.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms675652(v=vs.85).aspx
     *
     * @return string
     */
    public function getDriverName()
    {
        return $this->getFirstAttribute($this->schema->driverName());
    }

    /**
     * Returns the printer drivers version number.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms675653(v=vs.85).aspx
     *
     * @return string
     */
    public function getDriverVersion()
    {
        return $this->getFirstAttribute($this->schema->driverVersion());
    }

    /**
     * Returns the priority number of the printer.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679413(v=vs.85).aspx
     *
     * @return string
     */
    public function getPriority()
    {
        return $this->getFirstAttribute($this->schema->priority());
    }

    /**
     * Returns the printers start time.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679411(v=vs.85).aspx
     *
     * @return string
     */
    public function getPrintStartTime()
    {
        return $this->getFirstAttribute($this->schema->printerStartTime());
    }

    /**
     * Returns the printers end time.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679384(v=vs.85).aspx
     *
     * @return string
     */
    public function getPrintEndTime()
    {
        return $this->getFirstAttribute($this->schema->printerEndTime());
    }

    /**
     * Returns the port name of printer.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679131(v=vs.85).aspx
     *
     * @return string
     */
    public function getPortName()
    {
        return $this->getFirstAttribute($this->schema->portName());
    }

    /**
     * Returns the printers version number.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms680897(v=vs.85).aspx
     *
     * @return string
     */
    public function getVersionNumber()
    {
        return $this->getFirstAttribute($this->schema->versionNumber());
    }

    /**
     * Returns the print rate.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679405(v=vs.85).aspx
     *
     * @return string
     */
    public function getPrintRate()
    {
        return $this->getFirstAttribute($this->schema->printerPrintRate());
    }

    /**
     * Returns the print rate unit.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679406(v=vs.85).aspx
     *
     * @return string
     */
    public function getPrintRateUnit()
    {
        return $this->getFirstAttribute($this->schema->printerPrintRateUnit());
    }
}
